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

        $project = Project::factory()->create(['comment' => 'secret-comment']);

        $this->actingAs($admin)
            ->get(route('projects.show', $project))
            ->assertStatus(200)
            ->assertSeeText('secret-comment');

        $this->actingAs($pm)
            ->get(route('projects.show', $project))
            ->assertStatus(200)
            ->assertSeeText('secret-comment');

        // Admin can access edit
        $this->actingAs($admin)
            ->get(route('projects.edit', $project))
            ->assertStatus(200);

        // PM can access edit
        $this->actingAs($pm)
            ->get(route('projects.edit', $project))
            ->assertStatus(200);
    }

    public function test_project_comment_hidden_from_marketer_and_lawyer()
    {
        $pm = User::factory()->create(['role' => User::ROLE_PROJECT_MANAGER]);
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $lawyer = User::factory()->create(['role' => User::ROLE_LAWYER]);

        $project = Project::factory()->create(['marketer_id' => $marketer->id, 'comment' => 'secret-comment']);

        // Marketer (owner) can view project but must NOT see admin-only comment
        $this->actingAs($marketer)
            ->get(route('projects.show', $project))
            ->assertStatus(200)
            ->assertDontSeeText('secret-comment');

        // Create ProjectLawyer assignment for lawyer and ensure lawyer view also hides comment
        $pl = \App\Models\ProjectLawyer::create([
            'project_id' => $project->id,
            'user_id' => $lawyer->id,
            'sent_by' => $pm->id,
            'sent_at' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($lawyer)
            ->get(route('lawyer.projects.project', $pl))
            ->assertStatus(200)
            ->assertDontSeeText('secret-comment');
    }
}
