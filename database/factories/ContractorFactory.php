<?php

namespace Database\Factories;

use App\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'manager' => $this->faker->name,
            'email' => $this->faker->email,
            'address' => $this->faker->address,
            'invoice_prefix' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
