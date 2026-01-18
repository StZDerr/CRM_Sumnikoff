<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'city' => $this->faker->city,
            'importance_id' => null,
            'contract_amount' => $this->faker->numberBetween(10000, 1000000),
            'contract_date' => $this->faker->date(),
            'payment_type' => Project::PAYMENT_TYPE_PAID,
            'created_by' => null,
            'marketer_id' => null,
        ];
    }
}
