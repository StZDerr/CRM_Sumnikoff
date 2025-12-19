<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('login', $request->login)->first();

        if (! $user) {
            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'Пользователь с таким логином не найден.']);
        }

        if (! $user->email) {
            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'У этого пользователя не указан адрес электронной почты; сброс пароля по email недоступен.']);
        }

        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('login'))
                        ->withErrors(['login' => __($status)]);
    }
}
