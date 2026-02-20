<?php

use App\Models\User;
use App\Models\WorkDay;
use App\Models\WorkSession;
use App\Models\WorkBreak;
use Carbon\Carbon;

it('excludes break intervals from work_seconds when sessions overlap breaks', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-20 12:00:00'));

    $user = User::factory()->create(['role' => User::ROLE_MARKETER]);

    // create open work day for user
    $day = WorkDay::create([
        'user_id' => $user->id,
        'work_date' => Carbon::today()->toDateString(),
        'report' => '',
        'is_closed' => false,
    ]);

    // single open session that starts at 10:00 (and is still open)
    WorkSession::create([
        'work_day_id' => $day->id,
        'started_at' => Carbon::parse('2026-02-20 10:00:00'),
        'ended_at' => null,
        'minutes' => null,
    ]);

    // add a break that covers 10:00-11:00 (overlaps the session)
    WorkBreak::create([
        'work_day_id' => $day->id,
        'started_at' => Carbon::parse('2026-02-20 10:00:00'),
        'ended_at' => Carbon::parse('2026-02-20 11:00:00'),
        'minutes' => 60,
    ]);

    $resp = $this
        ->actingAs($user)
        ->get(route('work-time.state'))
        ->assertOk()
        ->json();

    // break = 3600s (10:00-11:00)
    expect($resp['break_seconds'])->toBe(3600);

    // work should be now()-11:00 == 3600s (12:00 - 11:00)
    expect($resp['work_seconds'])->toBe(3600);
});