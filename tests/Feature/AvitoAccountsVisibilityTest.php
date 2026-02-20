<?php

namespace Tests\Feature;

use App\Models\AvitoAccount;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvitoAccountsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketer_sees_own_and_unassigned_avito_accounts()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);
        $other = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $projectForMarketer = Project::factory()->create(['marketer_id' => $marketer->id]);
        $projectForOther = Project::factory()->create(['marketer_id' => $other->id]);

        $assignedToMarketer = AvitoAccount::create([
            'created_by' => $marketer->id,
            'label' => 'Marketer Account',
            'project_id' => $projectForMarketer->id,
            'oauth_data' => [],
            'is_active' => true,
        ]);

        $unassigned = AvitoAccount::create([
            'created_by' => $marketer->id,
            'label' => 'Unassigned Account',
            'project_id' => null,
            'oauth_data' => [],
            'is_active' => true,
        ]);

        $assignedToOther = AvitoAccount::create([
            'created_by' => $other->id,
            'label' => 'Other Marketer Account',
            'project_id' => $projectForOther->id,
            'oauth_data' => [],
            'is_active' => true,
        ]);

        $res = $this->actingAs($marketer)->get(route('avito.index'));

        $res->assertStatus(200)
            ->assertSeeText('Marketer Account')
            ->assertSeeText('Unassigned Account')
            ->assertDontSeeText('Other Marketer Account');
    }

    public function test_admin_sees_delete_button_and_can_delete_avito_account()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $account = AvitoAccount::create([
            'created_by' => $admin->id,
            'label' => 'To Be Deleted',
            'project_id' => null,
            'oauth_data' => [],
            'is_active' => true,
        ]);

        $res = $this->actingAs($admin)->get(route('avito.index'));
        $res->assertStatus(200)->assertSee(route('avito.accounts.destroy', $account));

        $this->actingAs($admin)
            ->delete(route('avito.accounts.destroy', $account))
            ->assertRedirect(route('avito.index'));

        $this->assertDatabaseMissing('avito_accounts', ['id' => $account->id]);
    }

    public function test_marketer_cannot_see_or_delete_avito_account()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $account = AvitoAccount::create([
            'created_by' => $marketer->id,
            'label' => 'Cannot Delete',
            'project_id' => null,
            'oauth_data' => [],
            'is_active' => true,
        ]);

        $res = $this->actingAs($marketer)->get(route('avito.index'));
        $res->assertStatus(200)->assertDontSee(route('avito.accounts.destroy', $account));

        $this->actingAs($marketer)
            ->delete(route('avito.accounts.destroy', $account))
            ->assertStatus(403);

        $this->assertDatabaseHas('avito_accounts', ['id' => $account->id]);
    }
}
