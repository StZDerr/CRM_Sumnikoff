<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition()
    {
        return [
            'expense_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'amount' => $this->faker->randomFloat(2, 10, 10000),
            'expense_category_id' => \App\Models\ExpenseCategory::orderBy('id')->value('id') ?? null,
            'organization_id' => null,
            'payment_method_id' => null,
            'bank_account_id' => null,
            'project_id' => Project::factory()->create()->id ?? null,
            'document_number' => null,
            'status' => 'paid',
            'description' => $this->faker->sentence(4),
            'currency' => 'RUB',
        ];
    }
}
