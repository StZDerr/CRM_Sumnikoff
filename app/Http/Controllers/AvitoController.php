<?php

namespace App\Http\Controllers;

use App\Models\AvitoAccount;
use App\Services\AvitoApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvitoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->isLawyer()) {
                abort(403, 'Доступ запрещён');
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $accounts = AvitoAccount::with(['project.marketer'])
            ->orderByDesc('id')
            ->paginate(12);

        $projects = \App\Models\Project::orderBy('title')->get();

        return view('admin.avito.index', compact('accounts', 'projects'));
    }

    public function store(Request $request, AvitoApiService $avitoApiService): RedirectResponse
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Только администратор может добавлять аккаунты Avito');
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'client_id' => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
        ]);

        try {
            $oauthData = $avitoApiService->issueClientCredentialsToken($data['client_id'], $data['client_secret']);

            $account = AvitoAccount::create([
                'created_by' => auth()->id(),
                'label' => $data['label'],
                'project_id' => $data['project_id'] ?? null,
                'oauth_data' => $oauthData,
                'profile_data' => null,
                'stats_data' => null,
                'is_active' => true,
            ]);

            $this->syncOne($account, $avitoApiService);
        } catch (\Throwable $e) {
            return redirect()->route('avito.index')
                ->with('error', 'Не удалось добавить аккаунт Avito: '.$e->getMessage())
                ->withInput();
        }

        return redirect()->route('avito.index')->with('success', 'Аккаунт Avito добавлен.');
    }

    public function sync(AvitoAccount $avitoAccount, AvitoApiService $avitoApiService): RedirectResponse
    {
        try {
            $this->syncOne($avitoAccount, $avitoApiService);
        } catch (\Throwable $e) {
            $this->saveSyncError($avitoAccount, $e);

            return redirect()->route('avito.index')->with('error', 'Ошибка синхронизации: '.$e->getMessage());
        }

        return redirect()->route('avito.index')->with('success', "Аккаунт {$avitoAccount->label} синхронизирован.");
    }

    public function syncAll(AvitoApiService $avitoApiService): RedirectResponse
    {
        $errors = [];

        AvitoAccount::query()->where('is_active', true)->chunkById(50, function ($accounts) use (&$errors, $avitoApiService) {
            foreach ($accounts as $account) {
                try {
                    $this->syncOne($account, $avitoApiService);
                } catch (\Throwable $e) {
                    $this->saveSyncError($account, $e);
                    $errors[] = "{$account->label}: {$e->getMessage()}";
                }
            }
        });

        if (! empty($errors)) {
            return redirect()->route('avito.index')->with('error', 'Часть аккаунтов не синхронизировалась: '.implode(' | ', $errors));
        }

        return redirect()->route('avito.index')->with('success', 'Все аккаунты Avito синхронизированы.');
    }

    protected function syncOne(AvitoAccount $account, AvitoApiService $avitoApiService): void
    {
        $result = $avitoApiService->syncAccount($account);

        $account->oauth_data = $result['oauth_data'];
        $account->profile_data = $result['profile_data'];
        $account->stats_data = $result['stats_data'];
        $account->last_synced_at = now();
        $account->save();
    }

    protected function saveSyncError(AvitoAccount $account, \Throwable $e): void
    {
        $stats = $account->stats_data ?? [];
        $stats['error'] = $e->getMessage();
        $stats['synced_at'] = now()->toDateTimeString();
        $account->stats_data = $stats;
        $account->save();
    }

    public function attachProject(Request $request, AvitoAccount $avitoAccount)
    {
        // allow admin and project manager to bind
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403, 'Доступ запрещён');
        }

        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        $avitoAccount->project_id = $data['project_id'];
        $avitoAccount->save();

        return redirect()->route('avito.index')->with('success', 'Проект привязан к аккаунту Avito.');
    }
}

