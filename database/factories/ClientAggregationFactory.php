<?php

namespace Database\Factories;

use App\Models\ClientAggregation;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientAggregationFactory extends Factory
{
    protected $model = ClientAggregation::class;

    public function definition()
    {
        return [
            'client_id' => $this->faker->numberBetween(1, 10),
            'type' => $this->faker->numberBetween(1, 2),
            'date' => $this->faker->dateTime(),
            'number_trans' => $this->faker->numberBetween(1, 10),
            'amount' => $this->faker->randomFloat(2, 0, 1000000),
            'settlement_fee' => $this->faker->randomFloat(2, 0, 100000),
            'number_refunds' => $this->faker->numberBetween(0, 5),
            'type_refund' => $this->faker->numberBetween(1, 6),
            'refund_amount' => $this->faker->randomFloat(2, 0, 100000),
            'refund_fee' => $this->faker->randomFloat(2, 0, 10000),
            'system_usage_fee' => $this->faker->randomFloat(2, 0, 10000),
            'account_number' => $this->faker->bankAccountNumber,
            'account_balance' => $this->faker->randomFloat(2, 0, 1000000),
            'memo' => $this->faker->sentence,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
