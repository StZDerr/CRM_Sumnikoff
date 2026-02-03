<?php

namespace App\Http\Controllers;

use App\Models\CampaignSource;
use App\Models\CampaignStatus;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except(['index', 'show']);
    }

    /**
     * Список организаций (поиск + фильтры по статусу/источнику)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $filterStatus = $request->query('status');
        $filterSource = $request->query('source');

        $query = Organization::query();

        $user = auth()->user();
        if ($user && $user->isMarketer()) {
            $query->whereHas('projects', function ($q) use ($user) {
                $q->where('marketer_id', $user->id);
            });
        }

        if ($q) {
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name_full', 'like', "%{$q}%")
                    ->orWhere('name_short', 'like', "%{$q}%")
                    ->orWhere('inn', 'like', "%{$q}%");
            });
        }

        if ($filterStatus) {
            $query->where('campaign_status_id', $filterStatus);
        }

        if ($filterSource) {
            $query->where('campaign_source_id', $filterSource);
        }

        $organizations = $query->orderBy('name_full')->paginate(25)->withQueryString();

        // Для фильтров показываем все статусы/источники (можно ограничить по организации при желании)
        $statuses = CampaignStatus::ordered()->pluck('name', 'id');
        $sources = CampaignSource::ordered()->pluck('name', 'id');

        return view('admin.organizations.index', compact('organizations', 'q', 'statuses', 'sources', 'filterStatus', 'filterSource'));
    }

    /**
     * Форма создания
     */
    public function create()
    {
        // Показываем глобальные статусы/источники (organization_id IS NULL)
        $statuses = CampaignStatus::ordered()->pluck('name', 'id');
        $sources = CampaignSource::ordered()->pluck('name', 'id');

        return view('admin.organizations.create', compact('statuses', 'sources'));
    }

    /**
     * Сохранение новой организации
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'entity_type' => 'nullable|in:individual,ip,ooo',
            'name_full' => 'required|string|max:500',
            'name_short' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'inn' => 'nullable|string|max:64|unique:organizations,inn',
            'kpp' => 'nullable|string|max:20',
            'ogrnip' => 'nullable|string|max:64|unique:organizations,ogrnip',
            'legal_address' => 'nullable|string',
            'actual_address' => 'nullable|string',
            'account_number' => 'nullable|string|max:64',
            'bank_name' => 'nullable|string|max:255',
            'corr_account' => 'nullable|string|max:64',
            'bic' => 'nullable|string|max:16',
            'notes' => 'nullable|string',
            'campaign_status_id' => 'nullable|exists:campaign_statuses,id',
            'campaign_source_id' => 'nullable|exists:campaign_sources,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt|max:5120',
        ]);

        $data['created_by'] = auth()->id();

        $organization = Organization::create($data);

        // Save uploaded documents if any
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $organization->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('organizations.index')->with('success', 'Организация создана.');
    }

    /**
     * AJAX: Создание организации (для модального окна)
     */
    public function storeAjax(Request $request)
    {
        $data = $request->validate([
            'entity_type' => 'nullable|in:individual,ip,ooo',
            'name_full' => 'required|string|max:500',
            'name_short' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'inn' => 'nullable|string|max:64|unique:organizations,inn',
            'kpp' => 'nullable|string|max:20',
            'ogrnip' => 'nullable|string|max:64|unique:organizations,ogrnip',
            'legal_address' => 'nullable|string',
            'actual_address' => 'nullable|string',
            'account_number' => 'nullable|string|max:64',
            'bank_name' => 'nullable|string|max:255',
            'corr_account' => 'nullable|string|max:64',
            'bic' => 'nullable|string|max:16',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt|max:5120',
        ]);

        $data['created_by'] = auth()->id();

        $organization = Organization::create($data);

        // Save uploaded documents if any
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $organization->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return response()->json([
            'success' => true,
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name_short ?: $organization->name_full,
            ],
        ]);
    }

    /**
     * Просмотр (включает контакты)
     */
    public function show(Organization $organization)
    {
        $user = auth()->user();
        if ($user && $user->isMarketer()) {
            $hasAccess = $organization->projects()
                ->where('marketer_id', $user->id)
                ->exists();

            if (! $hasAccess) {
                abort(403);
            }
        }

        $contacts = $organization->contacts()->orderBy('last_name')->paginate(25);

        return view('admin.organizations.show', compact('organization', 'contacts'));
    }

    /**
     * Форма редактирования
     */
    public function edit(Organization $organization)
    {
        // Передаём все статусы и источники (упорядоченные)
        $statuses = CampaignStatus::ordered()->pluck('name', 'id');
        $sources = CampaignSource::ordered()->pluck('name', 'id');

        return view('admin.organizations.edit', compact('organization', 'statuses', 'sources'));
    }

    /**
     * Обновление
     */
    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'entity_type' => 'nullable|in:individual,ip,ooo',
            'name_full' => 'required|string|max:500',
            'name_short' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'inn' => ['nullable', 'string', 'max:64', Rule::unique('organizations', 'inn')->ignore($organization->id)],
            'kpp' => 'nullable|string|max:20',
            'ogrnip' => ['nullable', 'string', 'max:64', Rule::unique('organizations', 'ogrnip')->ignore($organization->id)],
            'legal_address' => 'nullable|string',
            'actual_address' => 'nullable|string',
            'account_number' => 'nullable|string|max:64',
            'bank_name' => 'nullable|string|max:255',
            'corr_account' => 'nullable|string|max:64',
            'bic' => 'nullable|string|max:16',
            'notes' => 'nullable|string',
            'campaign_status_id' => 'nullable|exists:campaign_statuses,id',
            'campaign_source_id' => 'nullable|exists:campaign_sources,id',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt|max:5120',
        ]);

        $data['updated_by'] = auth()->id();

        $organization->update($data);

        // Save uploaded documents if any
        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $organization->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('organizations.index')->with('success', 'Организация обновлена.');
    }

    /**
     * Удаление (soft delete)
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('organizations.index')->with('success', 'Организация удалена.');
    }

    /**
     * Вернуть проекты организации в JSON (id, title)
     */
    public function projectsList(Organization $organization)
    {
        $currentUser = auth()->user();

        $query = $organization->projects()->orderBy('title');

        // Если маркетолог, показываем только проекты, где он назначен
        if ($currentUser->isMarketer()) {
            $query->where('marketer_id', $currentUser->id);
        }

        $projects = $query->get(['id', 'title']);

        return response()->json($projects);
    }

    /**
     * Загрузить документы для организации
     */
    public function storeDocument(Request $request, Organization $organization)
    {
        if (! auth()->user()->isAdmin()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'error' => 'Доступ запрещён'], 403);
            }
            abort(403);
        }

        $data = $request->validate([
            'documents' => 'required|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,txt|max:5120',
        ]);

        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('documents', 'public');
            $organization->documents()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('organizations.show', $organization)->with('success', 'Документы загружены.');
    }
}
