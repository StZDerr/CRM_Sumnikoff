<?php

use App\Models\User;
use App\Models\WorkDay;
use App\Models\WorkSession;
use Carbon\Carbon;

it('returns first session start as work_day.started_at', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-20 12:00:00'));

    $user = User::factory()->create(['role' => User::ROLE_MARKETER]);

    $day = WorkDay::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
        'report' => '',
        'is_closed' => false,
    ]);

    // earliest session 09:00
    WorkSession::create([
        'work_day_id' => $day->id,
        'started_at' => Carbon::parse('2026-02-20 09:00:00'),
        'ended_at' => Carbon::parse('2026-02-20 09:30:00'),
        'minutes' => 30,
    ]);

    // later session 10:00 (open)
    WorkSession::create([
        'work_day_id' => $day->id,
        'started_at' => Carbon::parse('2026-02-20 10:00:00'),
        'ended_at' => null,
        'minutes' => null,
    ]);

    $resp = $this
        ->actingAs($user)
        ->get(route('work-time.state'))
        ->assertOk()
        ->json();

    expect($resp['work_day']['started_at'])->toBe('2026-02-20T09:00');
});