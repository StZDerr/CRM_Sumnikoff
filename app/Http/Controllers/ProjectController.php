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
        $this->middleware(['auth', 'admin']);
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
            $query->whereNotNull('balance')->where('balance', '<', 0);
        } elseif ($balance_status === 'paid') {
            // сравниваем округлённый баланс с нулём
            $query->whereNotNull('balance')->whereRaw('ROUND(balance, 2) = 0');
        } elseif ($balance_status === 'overpaid') {
            $query->whereNotNull('balance')->where('balance', '>', 0);
        }

        // Сортировка: 1) должники (balance < 0) первыми, 2) по balance (самые большие долги — самые маленькие значения, т.е. -15000 перед -500), 3) по названию
        $projects = $query
            ->orderByRaw('(COALESCE(balance, 0) < 0) DESC')
            ->orderByRaw('COALESCE(balance, 0) ASC')
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
        $marketers = User::where('role', 'manager')->orderBy('name')->pluck('name', 'id');
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
            'payment_due_day' => 'nullable|integer|min:1|max:31',
            'comment' => 'nullable|string',
            'stages' => 'nullable|array',
            'stages.*' => 'integer|exists:stages,id',
        ]);

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
        Artisan::call('projects:update-debts', ['--project' => $project->id]);

        return redirect()->route('projects.index')->with('success', 'Проект создан.');
    }

    /**
     * Просмотр проекта
     */
    public function show(Project $project)
    {
        $project->load(['organization', 'marketer', 'paymentMethod', 'stages', 'importance', 'comments.user']);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Форма редактирования
     */
    public function edit(Project $project)
    {
        $organizations = Organization::orderBy('name_short')->pluck('name_short', 'id');
        $marketers = User::where('role', 'manager')->orderBy('name')->pluck('name', 'id');
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
            'payment_due_day' => 'nullable|integer|min:1|max:31',
            'comment' => 'nullable|string',
            'stages' => 'nullable|array',
            'stages.*' => 'integer|exists:stages,id',
        ]);

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
        if (array_key_exists('contract_amount', $data) || array_key_exists('contract_date', $data) || array_key_exists('closed_at', $data)) {
            Artisan::call('projects:update-debts', ['--project' => $project->id]);
        }

        return redirect()->route('projects.index')->with('success', 'Проект обновлён.');
    }

    /**
     * Удаление
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Проект удалён.');
    }
}
