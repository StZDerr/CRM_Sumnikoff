<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $items = BankAccount::orderBy('title')->paginate(25);

        return view('admin.bank_accounts.index', compact('items'));
    }

    public function create()
    {
        $bankAccount = new BankAccount;

        return view('admin.bank_accounts.create', compact('bankAccount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
            'correspondent_account' => 'nullable|string|max:255',
            'bik' => 'nullable|string|max:50',
            'inn' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        BankAccount::create($data);

        return redirect()->route('bank-accounts.index')->with('success', 'Банковский счёт создан.');
    }

    public function show(BankAccount $bankAccount)
    {
        return view('admin.bank_accounts.show', compact('bankAccount'));
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('admin.bank_accounts.edit', compact('bankAccount'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,'.$bankAccount->id,
            'correspondent_account' => 'nullable|string|max:255',
            'bik' => 'nullable|string|max:50',
            'inn' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $bankAccount->update($data);

        return redirect()->route('bank-accounts.index')->with('success', 'Банковский счёт обновлён.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')->with('success', 'Банковский счёт удалён.');
    }
}
