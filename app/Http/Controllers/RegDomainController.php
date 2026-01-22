<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Domain;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class RegDomainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(): View
    {
        $domains = Domain::with('project')
            ->orderBy('expires_at')
            ->orderBy('name')
            ->get();

        $projects = Project::orderBy('title')->get();

        $domainHostingCategories = ExpenseCategory::where('is_domains_hosting', true)->ordered()->get();
        $paymentMethods = PaymentMethod::orderBy('title')->get();
        $bankAccounts = BankAccount::orderBy('title')->get();
        $domainsForModal = Domain::where('provider', 'manual')->orderBy('name')->get();

        return view('admin.domains.index', compact(
            'domains',
            'projects',
            'domainHostingCategories',
            'paymentMethods',
            'bankAccounts',
            'domainsForModal'
        ));
    }

    public function create(): View
    {
        $projects = Project::orderBy('title')->get();

        return view('admin.domains.create', compact('projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:domains,name'],
            'status' => ['required', 'string', 'max:50'],
            'expires_at' => ['nullable', 'date'],
            'renew_price' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'auto_renew' => ['nullable', 'boolean'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $data['provider'] = 'manual';
        $data['provider_service_id'] = null;
        $data['auto_renew'] = (bool) ($data['auto_renew'] ?? false);

        Domain::create($data);

        return redirect()->route('domains.index')->with('success', 'Домен добавлен');
    }

    public function edit(Domain $domain): View
    {
        abort_unless($domain->provider === 'manual', 403);

        $projects = Project::orderBy('title')->get();

        return view('admin.domains.edit', compact('domain', 'projects'));
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        if ($domain->provider === 'reg_ru') {
            $data = $request->validate([
                'project_id' => ['nullable', 'exists:projects,id'],
            ]);

            $domain->update($data);

            return redirect()->route('domains.index')->with('success', 'Проект привязан');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:domains,name,'.$domain->id],
            'status' => ['required', 'string', 'max:50'],
            'expires_at' => ['nullable', 'date'],
            'renew_price' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'auto_renew' => ['nullable', 'boolean'],
            'project_id' => ['nullable', 'exists:projects,id'],
        ]);

        $data['auto_renew'] = (bool) ($data['auto_renew'] ?? false);

        $domain->update($data);

        return redirect()->route('domains.index')->with('success', 'Домен обновлён');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        abort_unless($domain->provider === 'manual', 403);

        $domain->delete();

        return redirect()->route('domains.index')->with('success', 'Домен удалён');
    }

    public function sync(): RedirectResponse
    {
        $exitCode = Artisan::call('reg:sync-domains');
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return redirect()->route('domains.index')
                ->with('error', $output ?: 'Ошибка синхронизации доменов');
        }

        return redirect()->route('domains.index')
            ->with('success', $output ?: 'Синхронизация выполнена');
    }

    public function renew(Request $request, Domain $domain): RedirectResponse
    {
        // Разрешаем продление только для доменов, созданных вручную
        abort_unless($domain->provider === 'manual', 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $amount = (float) $data['amount'];

        // Обновляем дату истечения и цену
        $expires = $domain->expires_at ? $domain->expires_at->copy()->addYear() : now()->addYear();
        $domain->expires_at = $expires;
        $domain->renew_price = $amount;
        $domain->save();

        return redirect()->route('domains.index')->with('success', "Домен {$domain->name} продлён на год");
    }
}
