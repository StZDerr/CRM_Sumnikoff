<?php

namespace Tests\Feature;

use App\Models\SalaryReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_users_in_index_and_can_edit()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'name' => 'Админ Тест']);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER, 'name' => 'Маркетолог Тест']);

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
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER, 'name' => 'Свой Тест']);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER, 'name' => 'Чужой Тест']);

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

    public function test_admin_and_pm_can_update_salary_report_comment()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $pm = User::factory()->create(['role' => User::ROLE_PROJECT_MANAGER]);
        $owner = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $report = SalaryReport::create([
            'user_id' => $owner->id,
            'month' => now()->startOfMonth()->toDateString(),
            'base_salary' => 100000,
            'ordinary_days' => 20,
            'remote_days' => 2,
            'audits_count' => 0,
            'custom_bonus' => 0,
            'individual_bonus' => 0,
            'fees' => 0,
            'penalties' => 0,
            'total_salary' => 100000,
            'status' => 'paid',
        ]);

        $this->actingAs($admin)
            ->post(route('attendance.comment', $report), ['comment' => 'admin comment'])
            ->assertRedirect();

        $this->assertDatabaseHas('salary_reports', [
            'id' => $report->id,
            'comment' => 'admin comment',
            'commented_by' => $admin->id,
        ]);

        $this->actingAs($pm)
            ->post(route('attendance.comment', $report), ['comment' => 'pm comment'])
            ->assertRedirect();

        $this->assertDatabaseHas('salary_reports', [
            'id' => $report->id,
            'comment' => 'pm comment',
            'commented_by' => $pm->id,
        ]);
    }

    public function test_marketer_cannot_update_comment_and_cannot_see_comment_block()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $report = SalaryReport::create([
            'user_id' => $marketer->id,
            'month' => now()->startOfMonth()->toDateString(),
            'base_salary' => 100000,
            'ordinary_days' => 20,
            'remote_days' => 2,
            'audits_count' => 0,
            'custom_bonus' => 0,
            'individual_bonus' => 0,
            'fees' => 0,
            'penalties' => 0,
            'total_salary' => 100000,
            'status' => 'paid',
            'comment' => 'secret comment',
        ]);

        $this->actingAs($marketer)
            ->get(route('attendance.show', $report))
            ->assertStatus(200)
            ->assertDontSeeText('Комментарий')
            ->assertDontSee('name="comment"', false)
            ->assertDontSeeText('secret comment');

        $this->actingAs($marketer)
            ->post(route('attendance.comment', $report), ['comment' => 'hack'])
            ->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('salary_reports', [
            'id' => $report->id,
            'comment' => 'secret comment',
        ]);
    }
}
