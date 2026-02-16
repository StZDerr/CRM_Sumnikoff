<?php

namespace App\Http\Controllers;

use App\Models\WorkBreak;
use App\Models\WorkDay;
use App\Models\WorkSession;
use App\Models\WorkTimeEdit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class WorkTimeController extends Controller
{
    public function state(Request $request)
    {
        $user = $request->user();
        $day = $this->getOpenDay($user->id);

        return response()->json($this->buildState($day));
    }

    public function startDay(Request $request)
    {
        $user = $request->user();
        $day = $this->getOpenDay($user->id);

        if (! $day) {
            $day = WorkDay::create([
                'user_id' => $user->id,
                'work_date' => now()->toDateString(),
                'report' => '',
                'is_closed' => false,
            ]);
        }

        $openBreak = $day->breaks()->whereNull('ended_at')->latest('started_at')->first();
        if ($openBreak) {
            return response()->json([
                'message' => 'День на паузе. Сначала снимите паузу.',
            ], 422);
        }

        $openSession = $day->sessions()->whereNull('ended_at')->latest('started_at')->first();
        if (! $openSession) {
            $day->sessions()->create([
                'started_at' => now(),
                'started_ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        $day->refresh();

        return response()->json($this->buildState($day));
    }

    public function startBreak(Request $request)
    {
        $user = $request->user();
        $day = $this->getOpenDay($user->id);

        if (! $day) {
            return response()->json(['message' => 'Сначала начните рабочий день.'], 422);
        }

        $openBreak = $day->breaks()->whereNull('ended_at')->latest('started_at')->first();
        if ($openBreak) {
            return response()->json(['message' => 'Пауза уже активна.'], 422);
        }

        $openSession = $day->sessions()->whereNull('ended_at')->latest('started_at')->first();
        if (! $openSession) {
            return response()->json(['message' => 'Нет активной рабочей сессии.'], 422);
        }

        $this->closeTimeItem($openSession, now());

        $day->breaks()->create([
            'started_at' => now(),
            'started_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->recalculateDayTotals($day);
        $day->refresh();

        return response()->json($this->buildState($day));
    }

    public function endBreak(Request $request)
    {
        $user = $request->user();
        $day = $this->getOpenDay($user->id);

        if (! $day) {
            return response()->json(['message' => 'Нет открытого рабочего дня.'], 422);
        }

        $openBreak = $day->breaks()->whereNull('ended_at')->latest('started_at')->first();
        if (! $openBreak) {
            return response()->json(['message' => 'Пауза не активна.'], 422);
        }

        $this->closeTimeItem($openBreak, now());

        $day->sessions()->create([
            'started_at' => now(),
            'started_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->recalculateDayTotals($day);
        $day->refresh();

        return response()->json($this->buildState($day));
    }

    public function endDay(Request $request)
    {
        $data = $request->validate([
            'ended_at' => ['required', 'date'],
            'report' => ['required', 'string', 'min:3'],
        ]);

        $user = $request->user();
        $day = $this->getOpenDay($user->id);

        if (! $day) {
            return response()->json(['message' => 'Нет открытого рабочего дня.'], 422);
        }

        $endAt = Carbon::parse($data['ended_at']);

        $openBreak = $day->breaks()->whereNull('ended_at')->latest('started_at')->first();
        if ($openBreak) {
            $this->closeTimeItem($openBreak, $endAt);
        }

        $openSession = $day->sessions()->whereNull('ended_at')->latest('started_at')->first();
        if ($openSession) {
            $this->closeTimeItem($openSession, $endAt);
        }

        $day->update([
            'report' => trim($data['report']),
            'is_closed' => true,
        ]);

        $this->recalculateDayTotals($day);

        return response()->json($this->buildState(null));
    }

    public function saveReport(Request $request)
    {
        $data = $request->validate([
            'report' => ['required', 'string', 'min:1'],
        ]);

        $user = $request->user();
        $day = $this->getOpenDay($user->id);

        if (! $day) {
            return response()->json(['message' => 'Нет открытого рабочего дня.'], 422);
        }

        $day->update([
            'report' => trim($data['report']),
        ]);

        $day->refresh();

        return response()->json($this->buildState($day));
    }

    public function editDayEnd(Request $request, WorkDay $workDay)
    {
        $data = $request->validate([
            'ended_at' => ['required', 'date'],
            'comment' => ['required', 'string', 'min:3'],
        ]);

        $this->ensureOwner($request->user()->id, $workDay->user_id);

        $session = $workDay->sessions()->latest('started_at')->first();
        if (! $session) {
            return response()->json(['message' => 'Нет сессии для редактирования.'], 422);
        }

        $newEndedAt = Carbon::parse($data['ended_at']);
        if ($newEndedAt->lt($session->started_at)) {
            $newEndedAt = $session->started_at->copy();
        }

        $oldStartedAt = $session->started_at;
        $oldEndedAt = $session->ended_at;

        $session->update([
            'ended_at' => $newEndedAt,
            'minutes' => $session->started_at->diffInMinutes($newEndedAt),
        ]);

        $this->logEdit(
            $request->user()->id,
            $session,
            $oldStartedAt,
            $oldEndedAt,
            $session->started_at,
            $session->ended_at,
            $data['comment']
        );

        $this->recalculateDayTotals($workDay);
        $workDay->refresh();

        $openDay = $this->getOpenDay($request->user()->id);

        return response()->json($this->buildState($openDay));
    }

    public function addBreak(Request $request, WorkDay $workDay)
    {
        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'comment' => ['required', 'string', 'min:3'],
        ]);

        $this->ensureOwner($request->user()->id, $workDay->user_id);

        $break = $workDay->breaks()->create([
            'started_at' => Carbon::parse($data['started_at']),
            'ended_at' => Carbon::parse($data['ended_at']),
            'minutes' => Carbon::parse($data['started_at'])->diffInMinutes(Carbon::parse($data['ended_at'])),
            'started_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        $this->logEdit(
            $request->user()->id,
            $break,
            null,
            null,
            $break->started_at,
            $break->ended_at,
            $data['comment']
        );

        $this->recalculateDayTotals($workDay);
        $workDay->refresh();

        $openDay = $this->getOpenDay($request->user()->id);

        return response()->json($this->buildState($openDay));
    }

    public function updateBreak(Request $request, WorkBreak $workBreak)
    {
        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'comment' => ['required', 'string', 'min:3'],
        ]);

        $workDay = $workBreak->workDay;
        $this->ensureOwner($request->user()->id, $workDay->user_id);

        $oldStartedAt = $workBreak->started_at;
        $oldEndedAt = $workBreak->ended_at;

        $startedAt = Carbon::parse($data['started_at']);
        $endedAt = Carbon::parse($data['ended_at']);

        $workBreak->update([
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'minutes' => $startedAt->diffInMinutes($endedAt),
        ]);

        $this->logEdit(
            $request->user()->id,
            $workBreak,
            $oldStartedAt,
            $oldEndedAt,
            $workBreak->started_at,
            $workBreak->ended_at,
            $data['comment']
        );

        $this->recalculateDayTotals($workDay);

        $openDay = $this->getOpenDay($request->user()->id);

        return response()->json($this->buildState($openDay));
    }

    public function deleteBreak(Request $request, WorkBreak $workBreak)
    {
        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3'],
        ]);

        $workDay = $workBreak->workDay;
        $this->ensureOwner($request->user()->id, $workDay->user_id);

        $this->logEdit(
            $request->user()->id,
            $workBreak,
            $workBreak->started_at,
            $workBreak->ended_at,
            null,
            null,
            $data['comment']
        );

        $workBreak->delete();
        $this->recalculateDayTotals($workDay);

        $openDay = $this->getOpenDay($request->user()->id);

        return response()->json($this->buildState($openDay));
    }

    private function getOpenDay(int $userId): ?WorkDay
    {
        return WorkDay::query()
            ->where('user_id', $userId)
            ->where('is_closed', false)
            ->with([
                'sessions' => fn ($q) => $q->orderBy('started_at'),
                'breaks' => fn ($q) => $q->orderBy('started_at'),
            ])
            ->orderByDesc('work_date')
            ->first();
    }

    private function closeTimeItem(Model $item, Carbon $endedAt): void
    {
        if (! $item->started_at) {
            return;
        }

        $effectiveEnd = $endedAt->copy();
        if ($effectiveEnd->lt($item->started_at)) {
            $effectiveEnd = $item->started_at->copy();
        }

        $item->update([
            'ended_at' => $effectiveEnd,
            'minutes' => $item->started_at->diffInMinutes($effectiveEnd),
        ]);
    }

    private function recalculateDayTotals(WorkDay $day): void
    {
        $workMinutes = (int) $day->sessions()->whereNotNull('ended_at')->sum('minutes');
        $breakMinutes = (int) $day->breaks()->whereNotNull('ended_at')->sum('minutes');

        $day->update([
            'total_work_minutes' => $workMinutes,
            'total_break_minutes' => $breakMinutes,
        ]);
    }

    private function ensureOwner(int $authUserId, int $ownerId): void
    {
        if ($authUserId !== $ownerId) {
            abort(403);
        }
    }

    private function logEdit(
        int $userId,
        Model $editable,
        ?Carbon $oldStartedAt,
        ?Carbon $oldEndedAt,
        ?Carbon $newStartedAt,
        ?Carbon $newEndedAt,
        string $comment
    ): void {
        WorkTimeEdit::create([
            'user_id' => $userId,
            'editable_id' => $editable->getKey(),
            'editable_type' => get_class($editable),
            'old_started_at' => $oldStartedAt,
            'old_ended_at' => $oldEndedAt,
            'new_started_at' => $newStartedAt,
            'new_ended_at' => $newEndedAt,
            'comment' => trim($comment),
        ]);
    }

    private function buildState(?WorkDay $day): array
    {
        if (! $day) {
            return [
                'mode' => 'idle',
                'work_seconds' => 0,
                'break_seconds' => 0,
                'work_day' => null,
                'breaks' => [],
                'edits' => [],
            ];
        }

        $openSession = $day->sessions->firstWhere('ended_at', null);
        $openBreak = $day->breaks->firstWhere('ended_at', null);

        $workSeconds = (int) $day->sessions
            ->filter(fn ($s) => $s->ended_at)
            ->sum(fn ($s) => ((int) $s->minutes) * 60);

        $breakSeconds = (int) $day->breaks
            ->filter(fn ($b) => $b->ended_at)
            ->sum(fn ($b) => ((int) $b->minutes) * 60);

        if ($openSession && $openSession->started_at) {
            $workSeconds += $openSession->started_at->diffInSeconds(now());
        }

        if ($openBreak && $openBreak->started_at) {
            $breakSeconds += $openBreak->started_at->diffInSeconds(now());
        }

        $mode = 'open';
        if ($openBreak) {
            $mode = 'paused';
        } elseif ($openSession) {
            $mode = 'working';
        }

        $editRows = WorkTimeEdit::query()
            ->whereHasMorph('editable', [WorkSession::class, WorkBreak::class], function ($q) use ($day) {
                $q->where('work_day_id', $day->id);
            })
            ->latest()
            ->limit(30)
            ->get()
            ->map(function (WorkTimeEdit $edit) {
                $type = $edit->editable_type === WorkBreak::class ? 'Пауза' : 'Рабочее время';

                return [
                    'type' => $type,
                    'comment' => $edit->comment,
                    'created_at' => optional($edit->created_at)->format('d.m.Y H:i'),
                ];
            })
            ->values();

        return [
            'mode' => $mode,
            'work_seconds' => $workSeconds,
            'break_seconds' => $breakSeconds,
            'work_day' => [
                'id' => $day->id,
                'work_date' => optional($day->work_date)->format('Y-m-d'),
                'report' => $day->report,
                'is_closed' => $day->is_closed,
                'suggested_end_at' => now()->format('Y-m-d\\TH:i'),
            ],
            'breaks' => $day->breaks->map(function (WorkBreak $break) {
                return [
                    'id' => $break->id,
                    'started_at' => optional($break->started_at)->format('Y-m-d\\TH:i'),
                    'ended_at' => optional($break->ended_at)->format('Y-m-d\\TH:i'),
                    'minutes' => $break->minutes,
                ];
            })->values(),
            'edits' => $editRows,
        ];
    }
}
