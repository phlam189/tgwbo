<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'bank_name' =>  $this->faker->company,
            'client_withdrawal_fee_1' =>  $this->faker->numberBetween(0, 100),
            'client_withdrawal_fee_2' =>  $this->faker->numberBetween(0, 100),
            'contract_withdrawal_fee_1' =>  $this->faker->numberBetween(0, 100),
            'contract_withdrawal_fee_2' =>  $this->faker->numberBetween(0, 100),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
