<?php

namespace App\Http\Controllers;

use App\Models\CampaignStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $statuses = CampaignStatus::ordered()->get();

        return view('admin.campaign_statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.campaign_statuses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:campaign_statuses,slug',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? '');
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (CampaignStatus::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $status = CampaignStatus::create($data);

        if (! empty($data['sort_order'])) {
            $status->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('campaign-statuses.index')->with('success', 'Статус кампании создан.');
    }

    public function edit(CampaignStatus $campaignStatus)
    {
        return view('admin.campaign_statuses.edit', compact('campaignStatus'));
    }

    public function update(Request $request, CampaignStatus $campaignStatus)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:campaign_statuses,slug,'.$campaignStatus->id,
            'sort_order' => 'nullable|integer|min:1',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name'] ?? $campaignStatus->name);
        }

        $campaignStatus->update($data);

        if (isset($data['sort_order']) && (int) $data['sort_order'] !== (int) $campaignStatus->sort_order) {
            $campaignStatus->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('campaign-statuses.index')->with('success', 'Статус обновлён.');
    }

    public function destroy(CampaignStatus $campaignStatus)
    {
        CampaignStatus::where('sort_order', '>', $campaignStatus->sort_order)->decrement('sort_order');
        $campaignStatus->delete();

        return redirect()->route('campaign-statuses.index')->with('success', 'Статус удалён.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:campaign_statuses,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            CampaignStatus::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
