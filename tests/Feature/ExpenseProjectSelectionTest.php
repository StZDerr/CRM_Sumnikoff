<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseProjectSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketer_create_form_shows_only_their_projects()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $ownProject = Project::factory()->create(['marketer_id' => $marketer->id, 'title' => 'own-project']);
        $otherProject = Project::factory()->create(['title' => 'other-project']);

        $res = $this->actingAs($marketer)->get(route('expenses.create'), ['X-Requested-With' => 'XMLHttpRequest']);
        $res->assertStatus(200)
            ->assertSeeText('own-project')
            ->assertDontSeeText('other-project');
    }
    public function test_create_form_preset_not_our_preselects_excluded_category()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $cat = ExpenseCategory::create(['title' => 'Not our', 'slug' => 'not-our', 'exclude_from_totals' => true]);

        $res = $this->actingAs($admin)
            ->get(route('expenses.create', ['preset' => 'not_our']), ['X-Requested-With' => 'XMLHttpRequest']);

        $res->assertStatus(200)
            ->assertSee('Not our')
            ->assertSee('selected');
    }
    public function test_marketer_cannot_store_expense_for_other_project()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $otherProject = Project::factory()->create();

        $res = $this->actingAs($marketer)->post(route('expenses.store'), [
            'expense_date' => now()->toDateTimeString(),
            'amount' => 100,
            'status' => 'paid',
            'project_id' => $otherProject->id,
        ]);

        $res->assertStatus(302); // redirect back with error
        $res->assertSessionHas('error');

        $this->assertDatabaseMissing('expenses', ['project_id' => $otherProject->id, 'amount' => 100]);
    }
}
