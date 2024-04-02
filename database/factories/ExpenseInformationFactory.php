<?php

namespace Database\Factories;

use App\Models\ExpenseInformation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseInformationFactory extends Factory
{
    protected $model = ExpenseInformation::class;

    public function definition()
    {
        return [
            'account_id' => $this->faker->numberBetween(1, 10),
            'client_id' => $this->faker->numberBetween(1, 10),
            'expense_date' =>  $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'expense_name' => $this->faker->word,
            'interest_rate' => $this->faker->randomFloat(2, 1, 10),
            'memo' => $this->faker->sentence,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
