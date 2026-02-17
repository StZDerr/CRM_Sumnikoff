<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = UserNotification::query()
            ->with(['project'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(UserNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($notification);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'Уведомление отмечено как прочитанное.');
    }

    public function markAllRead(): RedirectResponse
    {
        UserNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Все уведомления отмечены как прочитанные.');
    }

    public function destroy(UserNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($notification);

        if (auth()->user()->isMarketer()) {
            abort(403, 'Доступ запрещён');
        }

        $notification->delete();

        return back()->with('success', 'Уведомление скрыто.');
    }

    public function unreadCount()
    {
        $count = UserNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread' => $count]);
    }

    protected function authorizeNotification(UserNotification $notification): void
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);
    }
}
