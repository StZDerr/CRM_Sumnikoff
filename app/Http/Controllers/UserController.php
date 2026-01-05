<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): View
    {
        $users = User::with('activeVacation')->orderBy('id', 'desc')->paginate(15);
        $marketers = User::where('role', 'manager')->orderBy('name')->pluck('name', 'id');

        return view('admin.users.index', compact('users', 'marketers'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Пользователь создан');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Пользователь обновлён');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Запретить удаление самого себя
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Вы не можете удалить свой аккаунт');
        }

        DB::transaction(function () use ($user, &$reassignedCount, &$note) {
            // Найдём проекты, привязанные к удаляемому пользователю
            $projects = Project::where('marketer_id', $user->id)->get();

            $reassignedCount = 0;
            $note = null;

            if ($projects->isNotEmpty()) {
                // Получим доступных маркетологов (role = manager) кроме удаляемого
                $marketers = User::where('role', 'manager')
                    ->where('id', '<>', $user->id)
                    ->withCount('projects') // для балансировки
                    ->orderBy('projects_count', 'asc')
                    ->get()
                    ->values();

                if ($marketers->isEmpty()) {
                    // Нет маркетологов — обнулим поле marketer_id
                    foreach ($projects as $p) {
                        $p->update(['marketer_id' => null]);
                    }
                    $note = 'Проекты оставлены без маркетолога (нет доступных маркетологов).';
                } else {
                    // Распределим проекты по маркетологам в порядке минимальной загруженности
                    $idx = 0;
                    $mCount = $marketers->count();
                    // локальная карта загрузки (чтобы не ре-запрашивать каждый раз)
                    $loads = $marketers->pluck('projects_count')->toArray();

                    foreach ($projects as $p) {
                        // найдём индекс маркетолога с минимальной загрузкой
                        $minIdx = array_keys($loads, min($loads))[0];
                        $assignee = $marketers[$minIdx];
                        $p->update(['marketer_id' => $assignee->id]);
                        // увеличим локальную загрузку
                        $loads[$minIdx]++;
                        $reassignedCount++;
                    }

                    $note = "Проекты перераспределены между {$mCount} маркетологами.";
                }
            }

            // Удаляем пользователя (soft или hard в зависимости от модели)
            $user->delete();
        });

        $msg = 'Пользователь удалён.';
        if (! empty($reassignedCount)) {
            $msg .= " Переназначено проектов: {$reassignedCount}.";
        } elseif (! empty($note)) {
            $msg .= ' '.$note;
        }

        return redirect()->route('users.index')->with('success', $msg);
    }
}
