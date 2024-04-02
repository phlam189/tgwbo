<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $client_id = [3, 44, 20];
        $users = User::all();
        for ($i = 0; $i < count($client_id); $i++) {
            Client::factory()->create([
                'user_edit_id' => $users->random()->id,
                'client_id' => $client_id[$i],
            ]);
        }
    }
}
