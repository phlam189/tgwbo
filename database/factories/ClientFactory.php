<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Faker\Generator as Faker;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'represent_name' => $this->faker->name,
            'email' => $this->faker->email,
            'address' => $this->faker->address,
            'service_name' => $this->faker->name,
            'presence' => $this->faker->numberBetween(1, 10),
            'license_number' => $this->faker->unique()->numberBetween(1000, 9999),
            'total_year' => $this->faker->numberBetween(1, 10),
            'contractor_id' => $this->faker->numberBetween(1, 10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
