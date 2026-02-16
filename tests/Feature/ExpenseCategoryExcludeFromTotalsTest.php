<?php

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not count expenses from excluded category in dashboard totals', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $included = ExpenseCategory::create(['title' => 'Included', 'slug' => 'included', 'exclude_from_totals' => false]);
    $excluded = ExpenseCategory::create(['title' => 'Excluded', 'slug' => 'excluded', 'exclude_from_totals' => true]);

    \App\Models\Expense::create([
        'expense_date' => now(),
        'amount' => 1000,
        'expense_category_id' => $included->id,
        'status' => 'paid',
    ]);

    \App\Models\Expense::create([
        'expense_date' => now(),
        'amount' => 2000,
        'expense_category_id' => $excluded->id,
        'status' => 'paid',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertStatus(200)
        ->assertViewHas('monthTotalExpense', 1000.0);
});
