<?php

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Создаём пользователя
    $this->user = User::factory()->create();
});

describe('Office Expense Creation', function () {

    it('creates office expense with office category and null project_id', function () {
        // Создаём офисную категорию
        $officeCategory = ExpenseCategory::create([
            'title' => 'Канцтовары',
            'slug' => 'kantsvary',
            'is_office' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('expenses.store-office'), [
                'expense_date' => now()->format('Y-m-d H:i:s'),
                'amount' => 1500.00,
                'expense_category_id' => $officeCategory->id,
                'description' => 'Покупка бумаги для принтера',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Офисный расход сохранён.',
            ]);

        $this->assertDatabaseHas('expenses', [
            'expense_category_id' => $officeCategory->id,
            'project_id' => null,
            'amount' => 1500.00,
            'description' => 'Покупка бумаги для принтера',
        ]);
    });

    it('rejects non-office category for office expense', function () {
        // Создаём НЕ офисную категорию
        $normalCategory = ExpenseCategory::create([
            'title' => 'Реклама',
            'slug' => 'reklama',
            'is_office' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('expenses.store-office'), [
                'expense_date' => now()->format('Y-m-d H:i:s'),
                'amount' => 1000.00,
                'expense_category_id' => $normalCategory->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Категория должна быть офисной.',
            ]);
    });

    it('requires authentication for office expense creation', function () {
        $officeCategory = ExpenseCategory::create([
            'title' => 'Офисные расходы',
            'slug' => 'office',
            'is_office' => true,
        ]);

        $response = $this->postJson(route('expenses.store-office'), [
            'expense_date' => now()->format('Y-m-d H:i:s'),
            'amount' => 500.00,
            'expense_category_id' => $officeCategory->id,
        ]);

        $response->assertStatus(401);
    });

    it('validates required fields', function () {
        $response = $this->actingAs($this->user)
            ->postJson(route('expenses.store-office'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expense_date', 'amount', 'expense_category_id']);
    });

    it('validates minimum amount', function () {
        $officeCategory = ExpenseCategory::create([
            'title' => 'Тест',
            'slug' => 'test',
            'is_office' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('expenses.store-office'), [
                'expense_date' => now()->format('Y-m-d H:i:s'),
                'amount' => 0,
                'expense_category_id' => $officeCategory->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });
});

describe('Office Expense Filtering', function () {

    it('filters expenses by office category', function () {
        $officeCategory = ExpenseCategory::create([
            'title' => 'Офис',
            'slug' => 'office',
            'is_office' => true,
        ]);

        $normalCategory = ExpenseCategory::create([
            'title' => 'Реклама',
            'slug' => 'ad',
            'is_office' => false,
        ]);

        // Создаём офисный расход
        \App\Models\Expense::create([
            'expense_date' => now(),
            'amount' => 1000,
            'expense_category_id' => $officeCategory->id,
            'status' => 'paid',
        ]);

        // Создаём обычный расход
        \App\Models\Expense::create([
            'expense_date' => now(),
            'amount' => 2000,
            'expense_category_id' => $normalCategory->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('expenses.index', ['office' => 1]));

        $response->assertStatus(200);
        $response->assertSee('1 000'); // Офисный расход
        $response->assertDontSee('2 000'); // Обычный расход не должен показываться
    });
});

describe('Expense Category is_office field', function () {

    it('can mark category as office', function () {
        $response = $this->actingAs($this->user)
            ->post(route('expense-categories.store'), [
                'title' => 'Хозтовары',
                'is_office' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('expense_categories', [
            'title' => 'Хозтовары',
            'is_office' => true,
        ]);
    });

    it('can update category to office', function () {
        $category = ExpenseCategory::create([
            'title' => 'Тест категория',
            'slug' => 'test-cat',
            'is_office' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('expense-categories.update', $category), [
                'title' => 'Тест категория',
                'is_office' => true,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'is_office' => true,
        ]);
    });
});
