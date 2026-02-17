<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Avatar handling: upload new or remove existing
        $user = $request->user();

        // handle avatar upload (validate + create dir)
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            Log::info('ProfileController: avatar upload detected', ['user_id' => $user->id, 'error' => $file->getError()]);

            if (! $file->isValid()) {
                Log::warning('ProfileController: uploaded avatar is invalid', ['user_id' => $user->id, 'error' => $file->getError()]);
                return Redirect::back()->withErrors(['avatar' => 'Ошибка загрузки файла. Попробуйте другой файл.']);
            }

            // ensure directory exists
            if (! Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
            }

            $path = $file->store('avatars', 'public');

            if (! $path) {
                Log::error('ProfileController: failed to store avatar', ['user_id' => $user->id]);
                return Redirect::back()->withErrors(['avatar' => 'Не удалось сохранить файл.']);
            }

            // delete previous avatar after successful store
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $path;
        }

        if (! empty($data['remove_avatar'])) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = null;
        }

        $request->user()->fill($data);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        // ==== СОЦСЕТИ ====
        // Удаляем старые соцсети и создаем новые
        $request->user()->socials()->delete();

        if ($request->has('socials')) {
            foreach ($request->input('socials') as $social) {
                if (! empty($social['platform']) && ! empty($social['url'])) {
                    $raw = trim($social['url']);
                    if ($social['platform'] === 'telegram') {
                        if (str_contains($raw, 't.me')) {
                            if (! str_starts_with($raw, 'http')) {
                                $raw = 'https://'.$raw;
                            }
                            $parts = parse_url($raw);
                            $path = $parts['path'] ?? '';
                            $username = trim($path, "/@ \t\n\r\0\x0B");
                        } else {
                            $username = trim($raw, "/@ \t\n\r\0\x0B");
                        }

                        if ($username === '') {
                            continue;
                        }

                        $url = 'https://t.me/'.$username;
                    } else {
                        $url = $raw;
                    }

                    $request->user()->socials()->create([
                        'platform' => $social['platform'],
                        'url' => $url,
                    ]);
                }
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile information.
     */
    // public function update(ProfileUpdateRequest $request): RedirectResponse
    // {
    //     $request->user()->fill($request->validated());

    //     if ($request->user()->isDirty('email')) {
    //         $request->user()->email_verified_at = null;
    //     }

    //     $request->user()->save();

    //     return Redirect::route('profile.edit')->with('status', 'profile-updated');
    // }

    /**
     * Delete the user's account.
     */
    // public function destroy(Request $request): RedirectResponse
    // {
    //     $request->validateWithBag('userDeletion', [
    //         'password' => ['required', 'current_password'],
    //     ]);

    //     $user = $request->user();

    //     Auth::logout();

    //     $user->delete();

    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return Redirect::to('/');
    // }
}
