<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Organization;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Список (с поиском)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $contacts = Contact::with('organization')
            ->search($q)
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('admin.contacts.index', compact('contacts', 'q'));
    }

    /**
     * Форма создания
     */
    public function create()
    {
        $organizations = Organization::orderBy('name_full')->pluck('name_full', 'id');

        return view('admin.contacts.create', compact('organizations'));
    }

    /**
     * Сохранение
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'preferred_messenger' => 'nullable|in:telegram,whatsapp,viber,skype,call,other',
            'messenger_contact' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
        ]);

        $data['created_by'] = auth()->id();

        $contact = Contact::create($data);

        if (! empty($data['organization_id'])) {
            return redirect()->route('organizations.show', $data['organization_id'])->with('success', 'Контакт создан.');
        }

        return redirect()->route('contacts.index')->with('success', 'Контакт создан.');
    }

    /**
     * Просмотр
     */
    public function show(Contact $contact)
    {
        $contact->load('organization', 'createdBy', 'updatedBy');

        return view('admin.contacts.show', compact('contact'));
    }

    /**
     * Форма редактирования
     */
    public function edit(Contact $contact)
    {
        $organizations = Organization::orderBy('name_full')->pluck('name_full', 'id');

        return view('admin.contacts.edit', compact('contact', 'organizations'));
    }

    /**
     * Обновление
     */
    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'preferred_messenger' => 'nullable|in:telegram,whatsapp,viber,skype,call,other',
            'messenger_contact' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
        ]);

        $data['updated_by'] = auth()->id();

        $contact->update($data);

        if (! empty($data['organization_id'])) {
            return redirect()->route('organizations.show', $data['organization_id'])->with('success', 'Контакт обновлён.');
        }

        return redirect()->route('contacts.index')->with('success', 'Контакт обновлён.');
    }

    /**
     * Удаление
     */
    public function destroy(Contact $contact)
    {
        $orgId = $contact->organization_id;
        $contact->delete();

        if ($orgId) {
            return redirect()->route('organizations.show', $orgId)->with('success', 'Контакт удалён.');
        }

        return redirect()->route('contacts.index')->with('success', 'Контакт удалён.');
    }
}
