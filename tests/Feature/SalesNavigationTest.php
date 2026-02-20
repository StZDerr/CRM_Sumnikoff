<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_user_only_sees_bilain_and_lead_links_in_navigation()
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES]);

        $this->actingAs($sales)
            ->get(route('dashboard'))
            ->assertStatus(200)
            // links that should be visible
            ->assertSeeText('Dashboard')
            ->assertSeeText('Билайн')
            ->assertSeeText('Лиды')
            // links that must not appear
            ->assertDontSeeText('Сотрудники')
            ->assertDontSeeText('Операции')
            ->assertDontSeeText('Проекты')
            ->assertDontSeeText('Организации')
            ->assertDontSeeText('Финансы');
    }

    public function test_admin_sees_sales_links_and_other_sections()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSeeText('Билайн')
            ->assertSeeText('Лиды')
            ->assertSeeText('Сотрудники')
            ->assertSeeText('Операции');
    }

    public function test_non_sales_user_cannot_access_sales_routes()
    {
        $dev = User::factory()->create(['role' => User::ROLE_FRONTEND]);

        $this->actingAs($dev)
            ->get(route('lead.index'))
            ->assertStatus(403);

        $this->actingAs($dev)
            ->get(route('bilain.index'))
            ->assertStatus(403);
    }

    public function test_sales_and_admin_can_access_sales_routes()
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($sales)
            ->get(route('lead.index'))
            ->assertStatus(200);

        $this->actingAs($sales)
            ->get(route('bilain.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('lead.index'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('bilain.index'))
            ->assertStatus(200);
    }
}
