<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketer_sees_only_own_projects_on_index()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $ownProject = Project::factory()->create(['marketer_id' => $marketer->id]);
        $otherProject = Project::factory()->create(['marketer_id' => $other->id]);

        $this->actingAs($marketer)
            ->get(route('projects.index'))
            ->assertSeeText($ownProject->title)
            ->assertDontSeeText($otherProject->title);
    }

    public function test_marketer_can_view_own_project_but_cant_view_others()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $ownProject = Project::factory()->create(['marketer_id' => $marketer->id]);
        $otherProject = Project::factory()->create(['marketer_id' => $other->id]);

        $this->actingAs($marketer)
            ->get(route('projects.show', $ownProject))
            ->assertStatus(200);

        $this->actingAs($marketer)
            ->get(route('projects.show', $otherProject))
            ->assertStatus(403);
    }

    public function test_admin_and_pm_can_view_and_edit_any_project()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $pm = User::factory()->create(['role' => User::ROLE_PROJECT_MANAGER]);

        $project = Project::factory()->create();

        $this->actingAs($admin)
            ->get(route('projects.show', $project))
            ->assertStatus(200);

        $this->actingAs($pm)
            ->get(route('projects.show', $project))
            ->assertStatus(200);

        // Admin can access edit
        $this->actingAs($admin)
            ->get(route('projects.edit', $project))
            ->assertStatus(200);

        // PM can access edit
        $this->actingAs($pm)
            ->get(route('projects.edit', $project))
            ->assertStatus(200);
    }
}
