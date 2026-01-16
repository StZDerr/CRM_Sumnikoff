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
        $this->middleware(['auth', 'admin']);
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
        ]);

        $data['created_by'] = auth()->id();

        Organization::create($data);

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
        ]);

        $data['created_by'] = auth()->id();

        $organization = Organization::create($data);

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
        ]);

        $data['updated_by'] = auth()->id();

        $organization->update($data);

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
        $projects = $organization->projects()->orderBy('title')->get(['id', 'title']);

        return response()->json($projects);
    }
}
