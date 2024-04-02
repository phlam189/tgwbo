<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\TaskManagement;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateTaskManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new task management record';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $recentTask = TaskManagement::orderBy('date_sync', 'desc')
                ->first();
            if (!$recentTask) {
                $oldestDeposit = DB::connection('remote')->table('requests')->orderBy('date_requested')->first();
                $startDate = $oldestDeposit ? $oldestDeposit->date_requested : now()->startOfDay();
            } else {
                $startDate = $recentTask->date_sync->addDay();
            }
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $startDate)->startOfDay();
            $endDate = now()->startOfDay();
            $newTasks = [];
            $clients = Client::all('client_id')->where('client_id', '!=', '');
            for ($date = $startDate; $date->lt($endDate); $date->addDay()) {
                foreach ($clients as $client_id) {
                    $newTasks[] = [
                        'client_id' => $client_id->client_id,
                        'date_sync' => $date->format('Y-m-d H:i:s'),
                        'task_name' => $client_id->client_id . '_' . $date->format('Y-m-d'),
                        'status' => 0, // pending
                        'count' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($newTasks) {
                TaskManagement::insert($newTasks);
            }
            $this->info('Task management '. count($newTasks) .' record created successfully.');
        } catch (Exception $e) {
            $this->error('Error syncing data: ' . $e->getMessage());
            // Log the error
            Log::error('Error syncing data: ' . $e->getMessage());
        }
        return Command::SUCCESS;
    }
}
