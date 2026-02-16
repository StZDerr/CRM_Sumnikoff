<?php

use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores not-our expense with null payment method and bank account', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $category = ExpenseCategory::create([
        'title' => 'Прочие',
        'slug' => 'prochie',
        'is_office' => false,
        'is_salary' => false,
        'is_domains_hosting' => false,
        'exclude_from_totals' => true,
    ]);
    $project = Project::factory()->create();

    $response = $this->actingAs($admin)
        ->postJson(route('expenses.store-not-our'), [
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 3333.33,
            'expense_category_id' => $category->id,
            'project_id' => $project->id,
            'description' => 'Тест не наш расход',
        ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true]);

    $exists = \Illuminate\Support\Facades\DB::table('expenses')
        ->where('expense_category_id', $category->id)
        ->where('amount', 3333.33)
        ->whereNull('payment_method_id')
        ->whereNull('bank_account_id')
        ->where('project_id', $project->id)
        ->whereNull('organization_id')
        ->exists();

    expect($exists)->toBeTrue();
});

it('rejects office category for not-our expense', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $officeCategory = ExpenseCategory::create([
        'title' => 'Офис',
        'slug' => 'office-cat',
        'is_office' => true,
        'is_salary' => false,
        'is_domains_hosting' => false,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('expenses.store-not-our'), [
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 100,
            'expense_category_id' => $officeCategory->id,
        ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('rejects category without exclude_from_totals for not-our expense', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $category = ExpenseCategory::create([
        'title' => 'Обычная категория',
        'slug' => 'normal-cat',
        'is_office' => false,
        'is_salary' => false,
        'is_domains_hosting' => false,
        'exclude_from_totals' => false,
    ]);

    $response = $this->actingAs($admin)
        ->postJson(route('expenses.store-not-our'), [
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 100,
            'expense_category_id' => $category->id,
        ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('allows marketer to create not-our expense only for assigned project', function () {
    $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);

    $category = ExpenseCategory::create([
        'title' => 'Исключаемая категория',
        'slug' => 'excluded-cat',
        'is_office' => false,
        'is_salary' => false,
        'is_domains_hosting' => false,
        'exclude_from_totals' => true,
    ]);

    $assignedProject = Project::factory()->create(['marketer_id' => $marketer->id]);

    $response = $this->actingAs($marketer)
        ->postJson(route('expenses.store-not-our'), [
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 777,
            'expense_category_id' => $category->id,
            'project_id' => $assignedProject->id,
        ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true]);
});

it('rejects marketer not-our expense for unassigned project', function () {
    $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);

    $category = ExpenseCategory::create([
        'title' => 'Исключаемая категория 2',
        'slug' => 'excluded-cat-2',
        'is_office' => false,
        'is_salary' => false,
        'is_domains_hosting' => false,
        'exclude_from_totals' => true,
    ]);

    $foreignProject = Project::factory()->create();

    $response = $this->actingAs($marketer)
        ->postJson(route('expenses.store-not-our'), [
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 888,
            'expense_category_id' => $category->id,
            'project_id' => $foreignProject->id,
        ]);

    $response->assertStatus(403)
        ->assertJson(['success' => false]);
});
