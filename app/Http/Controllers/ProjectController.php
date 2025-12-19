<?php

namespace App\Http\Controllers;

use App\Models\Importance;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Http\Request;

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

        $projects = $query->orderBy('title')->paginate(25)->withQueryString();

        $organizations = Organization::orderBy('name_full')->pluck('name_full', 'id');
        $marketers = User::orderBy('name')->pluck('name', 'id');
        $importances = Importance::ordered()->pluck('name', 'id');

        return view('admin.projects.index', compact(
            'projects', 'q', 'organizations', 'marketers', 'org', 'marketer', 'importances', 'importance', 'contract_date'
        ));
    }

    /**
     * Форма создания
     */
    public function create()
    {
        $organizations = Organization::orderBy('name_full')->pluck('name_full', 'id');
        $marketers = User::orderBy('name')->pluck('name', 'id');
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
            'organization_id' => 'nullable|exists:organizations,id',
            'city' => 'nullable|string|max:255',
            'marketer_id' => 'nullable|exists:users,id',
            'importance_id' => 'nullable|exists:importances,id',
            'contract_amount' => 'nullable|numeric|min:0',
            'contract_date' => 'nullable|date', // <- валидация даты
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
        $organizations = Organization::orderBy('name_full')->pluck('name_full', 'id');
        $marketers = User::orderBy('name')->pluck('name', 'id');
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
            'organization_id' => 'nullable|exists:organizations,id',
            'city' => 'nullable|string|max:255',
            'marketer_id' => 'nullable|exists:users,id',
            'importance_id' => 'nullable|exists:importances,id',
            'contract_amount' => 'nullable|numeric|min:0',
            'contract_date' => 'nullable|date', // <- валидация даты
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
