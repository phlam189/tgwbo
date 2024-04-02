<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        return [
            'bank_id' => $this->faker->numberBetween(1, 10),
            'service_type' => $this->faker->numberBetween(1, 3),
            'category_name' => $this->faker->word . '_' . $this->faker->unique()->randomNumber(3),
            'bank_name' => $this->faker->company,
            'branch_name' => $this->faker->companySuffix,
            'representative_account' => $this->faker->name,
            'account_number' => $this->faker->bankAccountNumber,
            'account_holder' => $this->faker->name,
            'commission_rate' => $this->faker->randomFloat(2, 0, 10),
            'balance' => $this->faker->randomFloat(2, 0, 100000),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
