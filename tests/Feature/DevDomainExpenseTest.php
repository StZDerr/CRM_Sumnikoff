<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevDomainExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_dev_page_shows_add_domain_expense_button_and_modal_for_frontend()
    {
        $frontend = User::factory()->create(['role' => User::ROLE_FRONTEND]);

        // подготовим справочники, которые отображаются в модалке
        $cat = \App\Models\ExpenseCategory::create(['title' => 'Домены/Хостинг', 'slug' => 'domains-hosting', 'is_domains_hosting' => true]);
        $domain = \App\Models\Domain::create(['name' => 'example.test', 'provider' => 'reg_ru', 'status' => 'A']);
        $pm = \App\Models\PaymentMethod::create(['title' => 'Наличные', 'slug' => 'nalichnye']);
        $ba = \App\Models\BankAccount::create(['title' => 'Основной счёт', 'account_number' => '1234567890']);

        $resp = $this->actingAs($frontend)
            ->get(route('dev'))
            ->assertStatus(200)
            ->assertSee('id="openDomainHostingCategoriesBtn"', false)
            ->assertSee('id="domainHostingExpenseModal"', false)
            ->assertSeeText('Добавить')

            // проверим, что в модалке присутствуют опции заполненных справочников
            ->assertSeeText($cat->title)
            ->assertSeeText($domain->name)
            ->assertSeeText($pm->title)
            ->assertSeeText($ba->display_name);
    }
}
