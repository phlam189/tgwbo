<?php

namespace Database\Factories;

use App\Models\IntroducerInformation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class IntroducerInformationFactory extends Factory
{
    protected $model = IntroducerInformation::class;

    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'representative_name' => $this->faker->name,
            'email' => $this->faker->email,
            'address' => $this->faker->address,
            'contractor_id' => $this->faker->numberBetween(1, 10),
            'presence' => $this->faker->numberBetween(1, 10),
            'referral_classification' => $this->faker->numberBetween(1, 10),
            'referral_fee' => $this->faker->randomFloat(2, 1, 10),
            'contract_date' =>  $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
