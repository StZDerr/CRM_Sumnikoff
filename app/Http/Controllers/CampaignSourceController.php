<?php

namespace App\Http\Controllers;

use App\Models\CampaignSource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CampaignSourceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $sources = CampaignSource::ordered()->get();

        return view('admin.campaign_sources.index', compact('sources'));
    }

    public function create()
    {
        return view('admin.campaign_sources.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:campaign_sources,slug',
            'sort_order' => 'nullable|integer|min:1',
            'is_lead_source' => 'nullable|boolean',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? '');
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (CampaignSource::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $source = CampaignSource::create($data);

        if (! empty($data['sort_order'])) {
            $source->moveToPosition((int) $data['sort_order']);
        }

        // Если запрос AJAX/JSON — вернём данные в формате JSON для inline-форм
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'id' => $source->id, 'name' => $source->name], 201);
        }

        return redirect()->route('campaign-sources.index')->with('success', 'Источник создан.');
    }

    public function edit(CampaignSource $campaignSource)
    {
        return view('admin.campaign_sources.edit', compact('campaignSource'));
    }

    public function update(Request $request, CampaignSource $campaignSource)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('campaign_sources', 'slug')->ignore($campaignSource->id)],
            'sort_order' => 'nullable|integer|min:1',
            'is_lead_source' => 'nullable|boolean',
        ]);

        if (empty($data['slug'])) {
            $base = Str::slug($data['name'] ?? $campaignSource->name);
            $slug = $base ?: Str::random(6);
            $i = 1;
            while (CampaignSource::where('slug', $slug)->where('id', '<>', $campaignSource->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        $campaignSource->update($data);

        if (isset($data['sort_order']) && (int) $data['sort_order'] !== (int) $campaignSource->sort_order) {
            $campaignSource->moveToPosition((int) $data['sort_order']);
        }

        return redirect()->route('campaign-sources.index')->with('success', 'Источник обновлён.');
    }

    public function destroy(CampaignSource $campaignSource)
    {
        CampaignSource::where('sort_order', '>', $campaignSource->sort_order)->decrement('sort_order');
        $campaignSource->delete();

        return redirect()->route('campaign-sources.index')->with('success', 'Источник удалён.');
    }

    /**
     * Reorder via AJAX — принимает payload: { order: [id1, id2, ...] }
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:campaign_sources,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            CampaignSource::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
