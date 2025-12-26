<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');
        $q = trim($q);

        if ($q === '') {
            return response()->json(['results' => []]);
        }

        $limit = 7;

        $projects = Project::query()
            ->where('title', 'like', "%{$q}%")
            ->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ["{$q}%"])
            ->limit($limit)
            ->get(['id', 'title'])
            ->map(function ($p) {
                return [
                    'type' => 'project',
                    'id' => $p->id,
                    'label' => $p->title,
                    'url' => route('projects.show', $p),
                ];
            });

        $orgs = Organization::query()
            ->where('name_full', 'like', "%{$q}%")
            ->orderByRaw('CASE WHEN name_full LIKE ? THEN 0 ELSE 1 END', ["{$q}%"])
            ->limit($limit)
            ->get(['id', 'name_full'])
            ->map(function ($o) {
                return [
                    'type' => 'organization',
                    'id' => $o->id,
                    'label' => $o->name_full,
                    'url' => route('organizations.show', $o),
                ];
            });

        // Опционально: объединить и оставить первые N результатов
        $results = $projects->concat($orgs)->slice(0, 10)->values();

        if ($results->isEmpty()) {
            return response()->json(['results' => [], 'message' => 'Нет результатов']);
        }

        return response()->json(['results' => $results]);
    }
}
