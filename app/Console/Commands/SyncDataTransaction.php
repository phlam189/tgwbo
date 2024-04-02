<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Models\Bank;
use App\Models\ClientAggregation;
use App\Models\LogTask;
use App\Models\TaskManagement;
use App\Services\AccountBalanceHistoryService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncDataTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:data {task_id=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from remote database tgw to BO database';

    protected AccountBalanceHistoryService $accountBalanceHistoryService;

    public function __construct(AccountBalanceHistoryService $accountBalanceHistoryService)
    {
        parent::__construct();
        $this->accountBalanceHistoryService = $accountBalanceHistoryService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('start');
            $taskId = $this->argument('task_id');
            if ($taskId) {
                $tasks = TaskManagement::whereId($taskId)->orderBy('date_sync', 'asc')->get();
            } else {
                $tasks = TaskManagement::where('status', '!=', 2)->orderBy('date_sync', 'asc')->get();
            }
            foreach ($tasks as $task) {
                if ($task->count <= 3 || $taskId) {
                    try {
                        $requests = DB::connection('remote')->table('requests')
                            ->select(DB::raw('SUM(am_deposited) AS am_deposited, COUNT(*) as number_of_deposit'))
                            ->where('user_id', '=', $task->client_id)
                            ->where('date_deposited', '>=', $task->date_sync->startOfDay())
                            ->where('date_deposited', '<=', $task->date_sync->endOfDay())
                            ->where(function ($query) {
                                $query->where('status', '=', 'completed')
                                    ->orWhere('status', '=', 'on hold');
                            })->groupBy('user_id')->get();

                        $clientAggregation = [];
                        // get list from account to get bank fee
                        $accounts = $this->getListFromAccount($task);
                        foreach ($accounts as $account) {
                            $fromAccount = $account->from_account ?? 'account_' . $task->client_id;
                            $this->syncDataWithdrawals($task, $fromAccount);
                        }

                        if ($accounts->isEmpty()) {
                            $clientAgg = ClientAggregation::firstOrNew([
                                'client_id' => $task->client_id,
                                'date' => $task->date_sync,
                                'account_number' => 'account_' . $task->client_id,
                                'type' => 2,
                            ]);
                            $clientAgg->save();
                            $this->updateAccountBalanceHistory($task->client_id, 'account_' . $task->client_id, $task->date_sync, 2, 0);
                        }

                        // Get Account Deposit
                        $account = Account::query()->where('client_id', '=', $task->client_id)
                            ->where('service_type', '=', 1)->first();
                        $accountNumber = $account ? $account->account_number : 'deposit_' . $task->client_id;
                        $accountRate = $account ? $account->commission_rate : 0;

                        foreach ($requests as $request) {
                            if ($request->number_of_deposit) {
                                $clientAggCheck = ClientAggregation::where('client_id', '=', $task->client_id)
                                    ->where('date', '=', $task->date_sync)
                                    ->where('account_number', '=', $accountNumber)
                                    ->where('type', '=', 1)->get();
                                if ($clientAggCheck->isNotEmpty()) {
                                    foreach ($clientAggCheck as $client) {
                                        $updateClientAggregation = [
                                            'amount' => $request->am_deposited ?? 0,
                                            'number_trans' => $request->number_of_deposit,
                                            "account_fee" => $request->am_deposited * $client->account_usage_rate / 100, // truong hop update chi lay rate khi duoc tao record ko lay rate tren bang account
                                            'date' => $task->date_sync,
                                            'type' => 1,
                                        ];
                                        ClientAggregation::whereId($client->id)->update($updateClientAggregation);
                                        $this->accountBalanceHistoryService->reCalculateAccountBalance($task->client_id, $accountNumber, $task->date_sync, $request->am_deposited, 1);
                                        $this->info('Request Data sync updated successfully. Client ID:' . $task->client_id . ' Date:' . $task->date_sync);
                                        LogTask::insert(
                                            [
                                                'task_id' => $task->id,
                                                'message' => 'Request data sync updated successfully. Client ID:'
                                                    . $task->client_id . ' Date:' . $task->date_sync
                                                    . ' To: ' . $task->date_sync->addDay() . ' Account: ' . $accountNumber,
                                                'created_at' => now(),
                                                'updated_at' => now(),
                                            ]
                                        );
                                    }
                                }else {
                                    $clientAggregation[] = [
                                        'client_id' => $task->client_id,
                                        'amount' => $request->am_deposited ?? 0,
                                        'number_trans' => $request->number_of_deposit,
                                        'commission_bank_fee' => $request->bank_fee ?? 0,
                                        'display_amount' => 0,
                                        'date' => $task->date_sync,
                                        'account_number' => $accountNumber,
                                        "account_usage_rate" => $accountRate,
                                        "account_fee" => $request->am_deposited * $accountRate / 100,
                                        'type' => 1,
                                        'user_edit_id' => 0,
                                        'type_refund' => 0,
                                        'memo' => '',
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ];
                                    $this->updateAccountBalanceHistory($task->client_id, $accountNumber, $task->date_sync, 1, $request->am_deposited);
                                }
                            }
                        }

                        if ($requests->isEmpty()) {

                            $clientAgg = ClientAggregation::firstOrNew([
                                'client_id' => $task->client_id,
                                'date' => $task->date_sync,
                                'account_number' => $accountNumber,
                                'type' => 1,
                            ]);
                            $clientAgg->account_usage_rate = $accountRate;
                            $this->updateAccountBalanceHistory($task->client_id, $accountNumber, $task->date_sync, 1, 0);
                            $clientAgg->save();
                        }


                        if ($clientAggregation) {
                            ClientAggregation::insert($clientAggregation);
                            $this->info('Data sync completed successfully. Client ID:' . $task->client_id . ' Date:' . $task->date_sync);
                            LogTask::insert(
                                [
                                    'task_id' => $task->id,
                                    'message' => 'Data sync completed successfully. Client ID:' . $task->client_id . ' Date:' . $task->date_sync . ' To: ' . $task->date_sync->addDay(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]
                            );
                        }
                        TaskManagement::whereId($task->id)->update(
                            [
                                'status' => 2,
                                'count' => $task->count + 1
                            ]
                        );
                    } catch (Exception $e) {
                        TaskManagement::whereId($task->id)->update(
                            [
                                'status' => 1,
                                'count' => $task->count + 1
                            ]
                        );
                        LogTask::insert(
                            [
                                'task_id' => $task->id,
                                'message' => $e->getMessage(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                        $this->error('Error syncing data: ' . $e->getMessage());
                    }
                } else {
                    LogTask::insert(
                        [
                            'task_id' => $task->id,
                            'message' => 'This task need call re-sync. Client ID:' . $task->client_id . ' Date:' . $task->date_sync. ' To: ' .$task->date_sync->addDay(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }


        } catch (Exception $e) {
            $this->error('Error syncing data: ' . $e->getMessage());
            // Log the error
            Log::error('Error syncing data: ' . $e->getMessage());
        }
        return Command::SUCCESS;
    }

    public function getListFromAccount ($task) {
        return DB::connection('remote')->table('withdrawals')
            ->select(DB::raw('distinct from_account'))
            ->where('user_id', '=', $task->client_id)
            ->where('date_completed', '>=', $task->date_sync->startOfDay())
            ->where('date_completed', '<=', $task->date_sync->endOfDay())
            ->where('status', '=', 'completed')->get();
    }

    public function syncDataWithdrawals ($task, $fromAccount) {

        $withdrawals = DB::connection('remote')->table('withdrawals')
            ->where('user_id', '=', $task->client_id)
            ->where('date_completed', '>=', $task->date_sync->startOfDay())
            ->where('date_completed', '<=', $task->date_sync->endOfDay())
            ->where('status', '=', 'completed');
        $withdrawalsOtherBank = DB::connection('remote')->table('withdrawals')
            ->where('user_id', '=', $task->client_id)
            ->where('date_completed', '>=', $task->date_sync->startOfDay())
            ->where('date_completed', '<=', $task->date_sync->endOfDay())
            ->where('status', '=', 'completed')->groupBy('from_account');

        $bankFee1 = 0;
        $bankFee2 = 0;
        $withdrawals = $withdrawals->where('from_account', '=', $fromAccount);
        $withdrawalsOtherBank = $withdrawalsOtherBank->where('from_account', '=', $fromAccount);
        $bank = $this->getBankByAccount($fromAccount ?? 'account_' . $task->client_id);

        if ($bank) {
            $bankFee1 = $bank->client_withdrawal_fee_1;
            $bankFee2 = $bank->client_withdrawal_fee_2;

            $withdrawals->where(function ($query) use ($bank) {
                $bankList = $bank ? explode(',', $bank->bank_list_name) : null;
                foreach ($bankList as $item) {
                    if ($item != "") {
                        $query->orWhere('bank_name', 'like', '%' . $item . '%');
                    }
                }
            });


            $ids = DB::connection('remote')->table('withdrawals')
                ->select('id')
                ->where('user_id', '=', $task->client_id)
                ->where('date_completed', '>=', $task->date_sync->startOfDay())
                ->where('date_completed', '<=', $task->date_sync->endOfDay())
                ->where('status', '=', 'completed');
            $ids = $ids->where(function ($query) use ($bank) {
                $bankList = $bank ? explode(',', $bank->bank_list_name) : null;
                foreach ($bankList as $item) {
                    if ($item != "") {
                        $query->orWhere('bank_name', 'like', '%' . $item . '%');
                    }
                }
            })->get();

            $withdrawalsOtherBank->whereNotIn('id', $ids->pluck('id'));
        }
        $withdrawals = $withdrawals
            ->select(DB::raw('SUM(amount) AS amount, COUNT(*) as number_of_withdrawal,from_account,COUNT(*) * ' . $bankFee1 . ' as bank_fee'));
        $withdrawalsOtherBank = $withdrawalsOtherBank
            ->select(DB::raw('SUM(amount) AS amount, COUNT(*) as number_of_withdrawal,from_account,COUNT(*) * ' . $bankFee2 . ' as bank_fee'));

        $withdrawals = $withdrawals->groupBy('from_account')->get();
        $withdrawalsOtherBank = $bank ? $withdrawalsOtherBank->groupBy('from_account')->get() : collect([]);

        $withdrawals = collect([
            (object)[
                "amount" => ($withdrawals->first()->amount ?? 0) + ($withdrawalsOtherBank->first()->amount ?? 0),
                "number_trans_other_bank" => (int)($withdrawalsOtherBank->first()->number_of_withdrawal ?? 0),
                "transfer_fee_different" => (int)($withdrawalsOtherBank->first()->number_of_withdrawal ?? 0) * ($bank ? $bank->difference_fee : 0),
                "number_of_withdrawal" => (int)($withdrawals->first()->number_of_withdrawal ?? 0) + (int)($withdrawalsOtherBank->first()->number_of_withdrawal ?? 0),
                "from_account" => $fromAccount,
                "bank_fee" => ($withdrawals->first()->bank_fee ?? 0) + ($withdrawalsOtherBank->first()->bank_fee ?? 0),
                "account_usage_rate" => $bank ? $bank->commission_rate : 0,
                "account_fee" => (($withdrawals->first()->amount ?? 0) + ($withdrawalsOtherBank->first()->amount ?? 0)) * ($bank ? ($bank->commission_rate / 100) : 0)
            ]
        ]);

        $clientAggregation = [];
        foreach ($withdrawals as $withdrawal) {
            if ($withdrawal->number_of_withdrawal) {
                $clientAggCheck = ClientAggregation::where('client_id', '=', $task->client_id)
                    ->where('date', '=', $task->date_sync)
                    ->where('account_number', '=', $withdrawal->from_account ?? 'account_' . $task->client_id)
                    ->where('type', '=', 2)->get();
                if ($clientAggCheck->isNotEmpty()) {
                    foreach ($clientAggCheck as $client) {
                        $updateClientAggregation = [
                            'amount' => $withdrawal->amount ?? 0,
                            'number_trans' => $withdrawal->number_of_withdrawal,
                            'number_trans_other_bank' => $withdrawal->number_trans_other_bank,
                            'transfer_fee_different' => $withdrawal->transfer_fee_different,
                            'account_number' => $withdrawal->from_account ?? 'account_' . $task->client_id,
                            'commission_bank_fee' => $withdrawal->bank_fee,
                            "account_fee" => $withdrawal->amount * $client->account_usage_rate / 100,// truong hop update chi lay rate khi duoc tao record ko lay rate tren bang account
                            'date' => $task->date_sync,
                            'type' => 2,
                        ];
                        ClientAggregation::whereId($client->id)->update($updateClientAggregation);
                        $this->accountBalanceHistoryService->reCalculateAccountBalance($task->client_id, $withdrawal->from_account, $task->date_sync, $withdrawal->amount, 2);
                        $this->info('Withdrawal Data sync updated successfully. Client ID:' . $task->client_id . ' Date:' . $task->date_sync);
                        LogTask::insert(
                            [
                                'task_id' => $task->id,
                                'message' => 'Withdrawal data sync updated successfully. Client ID:'
                                    . $task->client_id . ' Date:' . $task->date_sync->startOfDay() . ' To: ' . $task->date_sync->endOfDay() . ' Account: ' . $withdrawal->from_account,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                } else {
                    $clientAggregation[] = [
                        'client_id' => $task->client_id,
                        'amount' => $withdrawal->amount ?? 0,
                        'number_trans' => $withdrawal->number_of_withdrawal,
                        'number_trans_other_bank' => $withdrawal->number_trans_other_bank,
                        'transfer_fee_different' => $withdrawal->transfer_fee_different,
                        'commission_bank_fee' => $withdrawal->bank_fee,
                        'display_amount' => 0,
                        'account_number' => $withdrawal->from_account ?? 'account_' . $task->client_id,
                        "account_usage_rate" => $withdrawal->account_usage_rate,
                        "account_fee" => $withdrawal->account_fee,
                        'date' => $task->date_sync,
                        'type' => 2,
                        'type_refund' => 0,
                        'memo' => '',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'user_edit_id' => 0
                    ];
                    $this->updateAccountBalanceHistory($task->client_id, $withdrawal->from_account ?? 'account_' . $task->client_id, $task->date_sync, 2, $withdrawal->amount ?? 0);
                }
            }
        }

        if ($clientAggregation) {
            ClientAggregation::insert($clientAggregation);
            $this->info('Data withdrawal sync completed successfully. Client ID:' . $task->client_id . ' Date:' . $task->date_sync);
            LogTask::insert(
                [
                    'task_id' => $task->id,
                    'message' => 'Data withdrawal sync completed successfully. Client ID:' . $task->client_id . ' Date:' . $task->date_sync . ' To: ' . $task->date_sync->addDay(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

    }

    public function getBankByAccount($fromAccount)
    {
        return Bank::query()->leftJoin('account', 'account.bank_id', 'bank.id')
            ->where('account.account_number', '=', $fromAccount)->first();

    }

    public function updateAccountBalanceHistory($clientId, $accountNumber, $dateHistory, $serviceType, $amount)
    {
        $accountBalance = AccountBalanceHistory::where('date_history', '<', $dateHistory)
            ->where('client_id', '=', $clientId)
            ->where('account_number', '=', $accountNumber)
            ->orderBy('date_history', 'desc')->first();
        $lastBalance = 0;
        if ($accountBalance) {
            $lastBalance = $accountBalance->balance;
        }

        $accountBalanceUpdate = AccountBalanceHistory::where('date_history', '=', $dateHistory)
            ->where('client_id', '=', $clientId)
            ->where('account_number', '=', $accountNumber)
            ->orderBy('date_history', 'desc')->first();

        if ($serviceType == 1) {
            $balance = $amount + $lastBalance;
        }

        if ($serviceType == 2) {
            $balance = $lastBalance - $amount;
        }

        if ($accountBalanceUpdate) {
            $accountBalanceUpdate->balance = $balance;
            $accountBalanceUpdate->save();
        } else {
            $accountBalance = AccountBalanceHistory::insert(
                [
                    'client_id' => $clientId,
                    'account_number' => $accountNumber,
                    'date_history' => $dateHistory,
                    'balance' => $balance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
