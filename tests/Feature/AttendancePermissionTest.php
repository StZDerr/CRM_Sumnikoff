<?php

namespace Tests\Feature;

use App\Models\AttendanceDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_users_in_index_and_can_edit()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $this->actingAs($admin)
            ->get(route('attendance.index'))
            ->assertStatus(200)
            ->assertSeeText($other->name);

        $date = now()->toDateString();
        $res = $this->actingAs($admin)->postJson(route('attendance.store'), [
            'user_id' => $other->id,
            'date' => $date,
            'status' => 'work',
        ]);

        $res->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('attendance_days', ['user_id' => $other->id]);
        $this->assertTrue(\Illuminate\Support\Facades\DB::table('attendance_days')->where('user_id', $other->id)->whereDate('date', $date)->exists());
    }

    public function test_marketer_sees_only_self_and_cannot_edit()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $this->actingAs($marketer)
            ->get(route('attendance.index'))
            ->assertStatus(200)
            ->assertSeeText($marketer->name)
            ->assertDontSeeText($other->name);

        $date = now()->toDateString();
        $res = $this->actingAs($marketer)->postJson(route('attendance.store'), [
            'user_id' => $marketer->id,
            'date' => $date,
            'status' => 'work',
        ]);

        $res->assertStatus(403);
        $this->assertDatabaseMissing('attendance_days', ['user_id' => $marketer->id, 'date' => $date]);
    }
}
