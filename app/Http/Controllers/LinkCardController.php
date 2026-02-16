<?php

namespace App\Http\Controllers;

use App\Models\LinkCard;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LinkCardController extends Controller
{
    // Список всех карточек пользователя
    public function index()
    {
        $projectId = request()->integer('project_id');

        if ($projectId) {
            $project = Project::findOrFail($projectId);
            Gate::authorize('view', $project);

            $cards = LinkCard::where('project_id', $project->id)
                ->orderBy('position')
                ->get();
        } else {
            $cards = LinkCard::where('user_id', auth()->id())
                ->whereNull('project_id')
                ->orderBy('position')
                ->get();
        }

        return response()->json($cards);
    }

    // Создание новой карточки
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'icon' => 'nullable|string|max:255',
            'position' => 'nullable|integer',
            'project_id' => 'nullable|integer|exists:projects,id',
        ]);

        $projectId = $request->integer('project_id');
        $project = null;
        if ($projectId) {
            $project = Project::findOrFail($projectId);
            Gate::authorize('view', $project);
        }

        $position = $request->position;
        if ($position === null) {
            $max = LinkCard::query()
                ->when($project, fn ($q) => $q->where('project_id', $project->id))
                ->when(! $project, fn ($q) => $q->where('user_id', auth()->id())->whereNull('project_id'))
                ->max('position');
            $position = (int) ($max ?? 0) + 1;
        }

        $card = LinkCard::create([
            'title' => $request->title,
            'url' => $request->url,
            'icon' => $request->icon,
            'position' => $position,
            'user_id' => $project ? null : auth()->id(),
            'project_id' => $project?->id,
        ]);

        // If icon not provided, try to fetch site favicon and store it
        if (empty($card->icon)) {
            try {
                $fetcherClass = 'App\\Services\\FaviconFetcher';
                if (class_exists($fetcherClass)) {
                    $fetcher = app($fetcherClass);
                    if (method_exists($fetcher, 'fetchAndStore')) {
                        $f = $fetcher->fetchAndStore($card->url);
                        if ($f) {
                            $card->icon = $f;
                            $card->save();
                        }
                    }
                }
            } catch (\Throwable $e) {
                // don't block creation on fetch errors
            }
        }

        if ($request->expectsJson()) {
            return response()->json($card, 201);
        }

        return redirect()->back()->with('success', 'Карточка добавлена');
    }

    // Просмотр одной карточки
    public function show(LinkCard $linkCard)
    {

        return response()->json($linkCard);
    }

    // Обновление карточки
    public function update(Request $request, LinkCard $linkCard)
    {
        if ($linkCard->project_id) {
            $project = Project::findOrFail($linkCard->project_id);
            Gate::authorize('view', $project);
        } else {
            if (auth()->id() !== $linkCard->user_id) {
                abort(403);
            }
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:255',
            'icon' => 'nullable|string|max:255',
            'position' => 'nullable|integer',
        ]);

        $oldUrl = $linkCard->url;
        $linkCard->update($request->only('title', 'url', 'icon', 'position'));

        // If icon was not provided or URL changed and icon is empty, try to fetch
        $shouldFetch = empty($linkCard->icon) && ($request->filled('url') && $request->input('url') !== $oldUrl || empty($linkCard->icon));
        if ($shouldFetch) {
            try {
                $fetcherClass = 'App\\Services\\FaviconFetcher';
                if (class_exists($fetcherClass)) {
                    $fetcher = app($fetcherClass);
                    if (method_exists($fetcher, 'fetchAndStore')) {
                        $f = $fetcher->fetchAndStore($linkCard->url);
                        if ($f) {
                            $linkCard->icon = $f;
                            $linkCard->save();
                        }
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return redirect()->back()->with('success', 'Карточка обновлена');
    }

    // Удаление карточки
    public function destroy(LinkCard $linkCard)
    {
        if ($linkCard->project_id) {
            $project = Project::findOrFail($linkCard->project_id);
            Gate::authorize('view', $project);
        } else {
            if (auth()->id() !== $linkCard->user_id) {
                abort(403);
            }
        }
        $linkCard->delete();

        return redirect()->back()->with('success', 'Карточка удалена');
    }

    // Обновление порядка карточек (drag & drop)
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|distinct|exists:link_cards,id',
            'project_id' => 'nullable|integer|exists:projects,id',
        ]);

        $ids = $request->order;
        $projectId = $request->integer('project_id');

        if ($projectId) {
            $project = Project::findOrFail($projectId);
            Gate::authorize('view', $project);

            $ownedCount = LinkCard::where('project_id', $projectId)
                ->whereIn('id', $ids)
                ->count();
        } else {
            $userId = auth()->id();
            $ownedCount = LinkCard::where('user_id', $userId)
                ->whereNull('project_id')
                ->whereIn('id', $ids)
                ->count();
        }

        if ($ownedCount !== count($ids)) {
            abort(403);
        }

        DB::transaction(function () use ($ids, $projectId) {
            foreach ($ids as $index => $id) {
                $query = LinkCard::where('id', $id);

                if ($projectId) {
                    $query->where('project_id', $projectId);
                } else {
                    $query->where('user_id', auth()->id())
                        ->whereNull('project_id');
                }

                $query->update(['position' => $index + 1]);
            }
        });

        return redirect()->back()->with('success', 'Порядок карточек обновлен');
    }
}
