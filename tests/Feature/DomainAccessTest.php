<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_sees_domains_link_and_can_access_index()
    {
        $frontend = User::factory()->create(['role' => User::ROLE_FRONTEND]);

        // Навигация (доступность ссылки) — верстальщик редиректится на `dev`
        $this->actingAs($frontend)
            ->get(route('dev'))
            ->assertStatus(200)
            ->assertSeeText('Домены');

        // Сам список доменов
        $this->actingAs($frontend)
            ->get(route('domains.index'))
            ->assertStatus(200)
            ->assertSeeText('Домены REG.RU');
    }
}
