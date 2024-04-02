<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        Bank::factory()->count(10)->create([
            'user_edit_id' => $users->random()->id,
        ]);
    }
}
