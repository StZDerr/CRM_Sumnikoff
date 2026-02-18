<?php

namespace App\Http\Controllers;

use App\Models\AccountCredential;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountCredentialController extends Controller
{
    public function __construct()
    {
        // Все методы требуют авторизации
        $this->middleware('auth');

        // Блокируем доступ ко всем методам контроллера для роли "lawyer"
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->isLawyer()) {
                abort(403, 'Доступ запрещён');
            }

            return $next($request);
        });
    }

    public function index(Project $project)
    {
        // Берем все доступы для конкретного проекта
        $credentials = AccountCredential::with(['user', 'updatedByUser', 'organization', 'project'])
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Передаем объект проекта во view
        return view('admin.account_credentials.index', compact('credentials', 'project'));
    }

    public function create(Project $project, Request $request)
    {
        $organization = $project->organization;

        return view('admin.account_credentials.create', compact('organization', 'project'));
    }

    public function createSite(Project $project, Request $request)
    {
        $organization = $project->organization;

        return view('admin.account_credentials.createSite', compact('organization', 'project'));
    }

    public function createBD(Project $project, Request $request)
    {
        $organization = $project->organization;

        return view('admin.account_credentials.createBD', compact('organization', 'project'));
    }

    public function createSSH(Project $project, Request $request)
    {
        $organization = $project->organization;

        return view('admin.account_credentials.createSSH', compact('organization', 'project'));
    }

    public function createFTP(Project $project, Request $request)
    {
        $organization = $project->organization;

        return view('admin.account_credentials.createFTP', compact('organization', 'project'));
    }

    public function createOther(Project $project, Request $request)
    {
        $organization = $project->organization;

        return view('admin.account_credentials.createOther', compact('organization', 'project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential['type'] = 'other';
        $credential->save();

        return redirect()->route('account-credentials.index', ['project' => $credential->project_id])
            ->with('success', 'Доступ создан успешно!');
    }

    public function storeSite(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);
        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential['type'] = 'site';
        $credential->save();

        return redirect()->route('account-credentials.index', ['project' => $credential->project_id])
            ->with('success', 'Доступ к сайту создан успешно!');
    }

    public function storeBD(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'db_name' => 'required|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);
        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential['type'] = 'bd';
        // dd($credential);
        $credential->save();

        return redirect()->route('account-credentials.index', ['project' => $credential->project_id])
            ->with('success', 'Доступ к БД создан успешно!');
    }

    public function storeSSH(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'server_ip' => 'required|string|max:255',   // IP сервера
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);
        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential['type'] = 'ssh';
        // Собираем полный логин: user@IP
        $credential->login = $request->login.'@'.$request->server_ip;
        $credential->save();

        return redirect()->route('account-credentials.index', ['project' => $credential->project_id])
            ->with('success', 'Доступ к SSH создан успешно!');
    }

    public function storeFTP(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);
        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential['type'] = 'ftp';
        $credential->save();

        return redirect()->route('account-credentials.index', ['project' => $credential->project_id])
            ->with('success', 'Доступ к FTP создан успешно!');
    }

    public function storeOther(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);
        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential['type'] = 'other';
        $credential->save();

        return redirect()->route('account-credentials.index', ['project' => $credential->project_id])
            ->with('success', 'Доступ создан успешно!');
    }

    public function show(AccountCredential $accountCredential)
    {
        // Логируем факт просмотра (пользователь увидел пароль на странице show)
        try {
            $accountCredential->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'view',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // не ломаем отображение страницы при ошибке логирования
            \Log::warning('AccountCredential::show - failed to write access log', ['id' => $accountCredential->id, 'error' => $e->getMessage()]);
        }

        $accountCredential->load(['logs.user']);

        return view('admin.account_credentials.show', compact('accountCredential'));
    }

    /**
     * Записать действие с доступом (AJAX)
     *
     * Дополнительно: если один пользователь просмотрел > 3 *уникальных* доступов за последний час,
     * отправляем уведомления администраторам (чтобы заметили потенциальный инцидент).
     */
    public function accessLog(AccountCredential $accountCredential, Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:view,reveal,copy_login,copy_password',
        ]);

        try {
            $accountCredential->logs()->create([
                'user_id' => auth()->id(),
                'action' => $request->action,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'context' => $request->input('context', null),
            ]);
        } catch (\Exception $e) {
            \Log::warning('AccountCredential::accessLog - failed to write access log', ['id' => $accountCredential->id, 'action' => $request->action, 'error' => $e->getMessage()]);

            return response()->json(['ok' => false], 500);
        }

        // Проверка на подозрительную активность: считаем уникальные просм. доступов за последний час
        try {
            $actorId = auth()->id();
            if ($actorId && in_array($request->action, ['view', 'reveal'], true)) {
                $since = now()->subHour();

                $uniqueCount = \App\Models\AccountCredentialLog::query()
                    ->where('user_id', $actorId)
                    ->whereIn('action', ['view', 'reveal'])
                    ->where('created_at', '>=', $since)
                    ->pluck('account_credential_id')
                    ->unique()
                    ->count();

                // порог: больше 3 (т.е. 3 и выше)
                if ($uniqueCount >= 3) {
                    // не шлём дублирующие уведомления чаще, чем раз в час для этого пользователя
                    $already = \App\Models\UserNotification::query()
                        ->where('actor_id', $actorId)
                        ->where('type', 'credential_views_alert')
                        ->where('created_at', '>=', $since)
                        ->exists();

                    if (! $already) {
                        $actor = auth()->user();
                        $actorName = $actor->name ?? $actor->login ?? $actorId;

                        $admins = \App\Models\User::admins()->get();
                        foreach ($admins as $admin) {
                            \App\Models\UserNotification::create([
                                'user_id' => $admin->id,
                                'actor_id' => $actorId,
                                'type' => 'credential_views_alert',
                                'title' => 'Подозрительная активность: просмотр доступов',
                                'message' => sprintf('Пользователь %s (%s) просмотрел %d разных доступов за последний час.', $actorName, $actor->login ?? '-', $uniqueCount),
                                'data' => [
                                    'offending_user_id' => $actorId,
                                    'count' => $uniqueCount,
                                    'window_minutes' => 60,
                                ],
                            ]);
                        }

                        \Log::info('Security alert: credential views threshold exceeded', ['user_id' => $actorId, 'count' => $uniqueCount]);
                    }
                }
            }
        } catch (\Exception $e) {
            // логируем, но не мешаем клиенту
            \Log::warning('AccountCredential::accessLog - alert check failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }

    public function edit(AccountCredential $accountCredential)
    {
        $accountCredential->load('project.organization');
        $organizations = Organization::all();
        $projects = Project::all();
        $project = $accountCredential->project;

        return view('admin.account_credentials.edit', compact('accountCredential', 'organizations', 'projects', 'project'));
    }

    public function update(Request $request, AccountCredential $accountCredential)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
            'organization_id' => 'required|exists:organizations,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        $accountCredential->fill($request->all());
        if ($request->password) {
            $accountCredential->setPasswordAttribute($request->password); // модель автоматически зашифрует
        }
        $accountCredential->updated_by = Auth::id();
        $accountCredential->save();

        return redirect()->route('account-credentials.index', ['project' => $accountCredential->project_id])
            ->with('success', 'Доступ обновлён!');
    }

    public function destroy(AccountCredential $accountCredential)
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }
        $accountCredential->delete(); // soft delete

        return back()->with('success', 'Доступ удалён!');
    }

    public function itSumnikoff()
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }

        $accountCredential = AccountCredential::whereNull('project_id')
            ->where('type', 'it_sumnikoff')
            ->get();

        return view('admin.account_credentials.it_sumnikoff', compact('accountCredential'));
    }

    public function createItSumnikoff()
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }

        return view('admin.account_credentials.createItSumnikoff');
    }

    public function showItSumnikoff(AccountCredential $accountCredential)
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }

        // Логируем факт просмотра
        try {
            $accountCredential->logs()->create([
                'user_id' => auth()->id(),
                'action' => 'view',
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('AccountCredential::showItSumnikoff - failed to write access log', ['id' => $accountCredential->id, 'error' => $e->getMessage()]);
        }

        return view('admin.account_credentials.showItSumnikoff', compact('accountCredential'));
    }

    public function editItSumnikoff(AccountCredential $accountCredential)
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }

        return view('admin.account_credentials.editItSumnikoff', compact('accountCredential'));
    }

    public function storeItSumnikoff(Request $request)
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
        ]);

        $credential = new AccountCredential($request->all());
        $credential->user_id = Auth::id();
        $credential->project_id = null;
        $credential['type'] = 'it_sumnikoff';
        $credential->save();

        return redirect()->route('account-credentials.itSumnikoff')
            ->with('success', 'Доступ создан успешно!');
    }

    public function updateItSumnikoff(Request $request, AccountCredential $accountCredential)
    {
        if (! auth()->user()?->isAdmin() && ! auth()->user()?->isProjectManager()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255',
            'password' => 'required|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,stop_list',
        ]);

        $credential = $accountCredential;
        $credential->fill($request->all());
        $credential->user_id = Auth::id();
        $credential->updated_by = Auth::id();
        $credential->project_id = null;
        $credential['type'] = 'it_sumnikoff';
        $credential->save();

        return redirect()->route('account-credentials.itSumnikoff')
            ->with('success', 'Доступ создан успешно!');
    }
}
