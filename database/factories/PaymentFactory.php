<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'project_id' => Project::factory()->create()->id ?? null,
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payment_method_id' => null,
            'invoice_id' => null,
            'transaction_id' => null,
            'note' => $this->faker->sentence(5),
            'bank_account_id' => null,
            'payment_category_id' => null,
            'vat_amount' => 0,
            'usn_amount' => 0,
            'created_by' => null,
        ];
    }
}
