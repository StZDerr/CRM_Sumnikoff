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

    public function show(AccountCredential $accountCredential)
    {
        return view('admin.account_credentials.show', compact('accountCredential'));
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
        $accountCredential->delete(); // soft delete

        return back()->with('success', 'Доступ удалён!');
    }
}
