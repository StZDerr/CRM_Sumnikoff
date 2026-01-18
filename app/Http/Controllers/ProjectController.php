<?php

namespace App\Http\Controllers;

use App\Models\Importance;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ProjectController extends Controller
{
    public function __construct()
    {
        // Разрешаем просмотр всем авторизованным пользователям. Создание/редактирование/удаление только для admin и project_manager
        $this->middleware(['auth']);
        $this->middleware(['role:admin,project_manager'])->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Список проектов (поиск, фильтры, пагинация)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $org = $request->query('organization');
        $marketer = $request->query('marketer');
        $importance = $request->query('importance');
        $contract_date = $request->query('contract_date'); // <- фильтр по дате

        $balance_status = $request->query('balance_status');

        $query = Project::with(['organization', 'marketer', 'paymentMethod', 'stages', 'importance']);

        // Если пользователь — маркетолог, показываем только проекты, где он назначен
        if (auth()->user()?->isMarketer()) {
            $query->where('marketer_id', auth()->id());
        }

        if ($q) {
            $query->where('title', 'like', '%'.$q.'%');
        }

        if ($org) {
            $query->where('organization_id', $org);
        }

        if ($marketer) {
            $query->where('marketer_id', $marketer);
        }

        if ($importance) {
            $query->where('importance_id', $importance);
        }

        if ($contract_date) {
            $query->whereDate('contract_date', $contract_date);
        }

        if ($balance_status === 'debt') {
            // Долг: сумма счетов > суммы платежей (т.е. платежи - счета < 0)
            $query->whereRaw('(
                COALESCE((SELECT SUM(amount) FROM payments WHERE payments.project_id = projects.id), 0) -
                COALESCE((SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id), 0)
            ) < 0')
                ->whereRaw('(SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id) > 0');
        } elseif ($balance_status === 'paid') {
            // Оплачено: сумма счетов = сумме платежей
            $query->whereRaw('ROUND(
                COALESCE((SELECT SUM(amount) FROM payments WHERE payments.project_id = projects.id), 0) -
                COALESCE((SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id), 0)
            , 2) = 0')
                ->whereRaw('(SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id) > 0');
        } elseif ($balance_status === 'overpaid') {
            // Переплата: сумма платежей > суммы счетов
            $query->whereRaw('(
                COALESCE((SELECT SUM(amount) FROM payments WHERE payments.project_id = projects.id), 0) -
                COALESCE((SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id), 0)
            ) > 0')
                ->whereRaw('(SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id) > 0');
        }

        // Подзапросы для сортировки по рассчитанному балансу
        $balanceSubquery = '(
            COALESCE((SELECT SUM(amount) FROM payments WHERE payments.project_id = projects.id), 0) -
            COALESCE((SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id), 0)
        )';
        $hasInvoicesSubquery = '(SELECT SUM(amount) FROM invoices WHERE invoices.project_id = projects.id)';

        // Сортировка: 1) должники первыми (у кого есть счета и баланс < 0), 2) по балансу, 3) по названию
        $projects = $query
            ->orderByRaw("(COALESCE({$hasInvoicesSubquery}, 0) > 0 AND {$balanceSubquery} < 0) DESC")
            ->orderByRaw("{$balanceSubquery} ASC")
            ->orderBy('title')
            ->paginate(25)
            ->withQueryString();

        $organizations = Organization::orderBy('name_full')->pluck('name_full', 'id');
        $marketers = User::orderBy('name')->pluck('name', 'id');
        $importances = Importance::ordered()->pluck('name', 'id');

        return view('admin.projects.index', compact(
            'projects', 'q', 'organizations', 'marketers', 'org', 'marketer', 'importances', 'importance', 'contract_date', 'balance_status'
        ));
    }

    /**
     * Форма создания
     */
    public function create()
    {
        $organizations = Organization::orderBy('name_short')->pluck('name_short', 'id');
        $marketers = User::whereIn('role', [User::ROLE_PROJECT_MANAGER, User::ROLE_MARKETER])->orderBy('name')->pluck('name', 'id');
        $stages = Stage::ordered()->pluck('name', 'id');
        $paymentMethods = PaymentMethod::ordered()->pluck('title', 'id');
        $importances = Importance::ordered()->pluck('name', 'id');

        return view('admin.projects.create', compact('organizations', 'marketers', 'stages', 'paymentMethods', 'importances'));
    }

    /**
     * Сохранение проекта
     */
    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create', Project::class);

        $data = $request->validate([ 
            'title' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'city' => 'required|string|max:255',
            'closed_at' => 'nullable|date|after_or_equal:contract_date',
            'marketer_id' => 'nullable|exists:users,id',
            'importance_id' => 'required|exists:importances,id',
            'contract_amount' => 'required|numeric|min:0',
            'contract_date' => 'required|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_type' => 'nullable|in:paid,barter,own',
            'payment_due_day' => 'nullable|integer|min:1|max:31',
            'comment' => 'nullable|string',
            'stages' => 'nullable|array',
            'stages.*' => 'integer|exists:stages,id',
        ]);

        $data['created_by'] = auth()->id();

        $project = Project::create($data);

        // Синхронизируем этапы с порядком (если передали)
        if (! empty($data['stages'])) {
            $sync = [];
            foreach ($data['stages'] as $idx => $stageId) {
                $sync[$stageId] = ['sort_order' => $idx + 1];
            }
            $project->stages()->sync($sync);
        }

        // Запустить пересчёт долгов/поступлений для этого проекта
        // Artisan::call('projects:update-debts', ['--project' => $project->id]);

        return redirect()->route('projects.index')->with('success', 'Проект создан.');
    }

    /**
     * Просмотр проекта
     */
    public function show(Project $project)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $project);

        $project->load(['organization', 'marketer', 'paymentMethod', 'stages', 'importance', 'comments.user']);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Форма редактирования
     */
    public function edit(Project $project)
    {
        $organizations = Organization::orderBy('name_short')->pluck('name_short', 'id');
        $marketers = User::whereIn('role', [User::ROLE_PROJECT_MANAGER, User::ROLE_MARKETER])->orderBy('name')->pluck('name', 'id');
        $stages = Stage::ordered()->pluck('name', 'id');
        $paymentMethods = PaymentMethod::ordered()->pluck('title', 'id');
        $importances = Importance::ordered()->pluck('name', 'id');

        // Получаем текущие этапы в порядке (по pivot.sort_order)
        $currentStages = $project->stages()->orderBy('project_stage.sort_order')->pluck('stages.id')->toArray();

        return view('admin.projects.edit', compact(
            'project', 'organizations', 'marketers', 'stages', 'paymentMethods', 'importances', 'currentStages'
        ));
    }

    /**
     * Обновление проекта
     */
    public function update(Request $request, Project $project)
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $project);

        $data = $request->validate([ 
            'title' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            'city' => 'required|string|max:255',
            'closed_at' => 'nullable|date|after_or_equal:contract_date',
            'marketer_id' => 'required|exists:users,id',
            'importance_id' => 'required|exists:importances,id',
            'contract_amount' => 'required|numeric|min:0',
            'contract_date' => 'required|date',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_type' => 'nullable|in:paid,barter,own',
            'payment_due_day' => 'nullable|integer|min:1|max:31',
            'comment' => 'nullable|string',
            'stages' => 'nullable|array',
            'stages.*' => 'integer|exists:stages,id',
        ]);

        $data['updated_by'] = auth()->id();

        $project->update($data);

        // Синхронизируем этапы (с порядком)
        if (isset($data['stages'])) {
            $sync = [];
            foreach ($data['stages'] as $idx => $stageId) {
                $sync[$stageId] = ['sort_order' => $idx + 1];
            }
            $project->stages()->sync($sync);
        }

        // Если изменилось contract_amount/contract_date/closed_at — пересчитать для проекта
        // if (array_key_exists('contract_amount', $data) || array_key_exists('contract_date', $data) || array_key_exists('closed_at', $data)) {
        //     Artisan::call('projects:update-debts', ['--project' => $project->id]);
        // }

        return redirect()->route('projects.index')->with('success', 'Проект обновлён.');
    }

    /**
     * Удаление
     */
    public function destroy(Project $project)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Проект удалён.');
    }
}
