<?php

namespace Database\Seeders;

use App\Models\ExpenseInformation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        ExpenseInformation::factory()->count(10)->create([
            'user_edit_id' => $users->random()->id,
        ]);
    }
}
