<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_payments_and_expenses()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $project = Project::factory()->create();
        Payment::factory()->create(['project_id' => $project->id, 'amount' => 100]);
        Expense::factory()->create(['project_id' => $project->id, 'amount' => 50]);

        $this->actingAs($admin)
            ->get(route('operation.index'))
            ->assertStatus(200)
            ->assertSeeText('Поступление')
            ->assertSeeText('Расход');
    }

    public function test_project_manager_sees_only_expenses_not_payments()
    {
        $pm = User::factory()->create(['role' => User::ROLE_PROJECT_MANAGER]);
        $project = Project::factory()->create();
        Payment::factory()->create(['project_id' => $project->id, 'amount' => 100]);
        Expense::factory()->create(['project_id' => $project->id, 'amount' => 50]);

        $this->actingAs($pm)
            ->get(route('operation.index'))
            ->assertStatus(200)
            ->assertDontSeeText('Поступление')
            ->assertSeeText('Расход');
    }

    public function test_marketer_sees_only_expenses_of_their_projects()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $ownProject = Project::factory()->create(['marketer_id' => $marketer->id]);
        $otherProject = Project::factory()->create();

        Expense::factory()->create(['project_id' => $ownProject->id, 'amount' => 30, 'description' => 'own']);
        Expense::factory()->create(['project_id' => $otherProject->id, 'amount' => 20, 'description' => 'other']);
        Payment::factory()->create(['project_id' => $ownProject->id, 'amount' => 100]);

        $this->actingAs($marketer)
            ->get(route('operation.index'))
            ->assertStatus(200)
            ->assertSeeText('own')
            ->assertDontSeeText('other')
            ->assertDontSeeText('Поступление');
    }
}
