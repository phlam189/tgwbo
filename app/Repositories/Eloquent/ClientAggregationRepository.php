<?php

namespace App\Repositories\Eloquent;

use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Models\ChargeHistory;
use App\Models\ClientAggregation;
use App\Repositories\Interfaces\ClientAggregationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClientAggregationRepository extends BaseRepository implements ClientAggregationRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return ClientAggregation::class;
    }

    public function getClientAggregationByClientId($clientId, $fromDate = null)
    {
        $query = $this->getQuery()->where('client_id', $clientId);
        if ($fromDate) {
            $query->where('date', '>=', $fromDate);
        }
        return $query->orderBy('date')->get();
    }

    private function getFromDateByPage ($page): string
    {
        if($page > 1) {
            return Carbon::now()->subMonth($page - 1)->startOfMonth()->format('Y-m-d H:i:s');
        }
        return Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
    }

    private function getToDateByPage($page): string
    {
        if ($page > 1) {
            return Carbon::now()->subMonth($page - 1)->endOfMonth()->format('Y-m-d H:i:s');
        }
        return Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
    }

    public function getTransactionByDate(Request $request)
    {
        $query = $this->clearQuery();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfYear()->format('Y-m-d H:i:s');
        $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfYear()->format('Y-m-d H:i:s');
        $query->where('date', '>=', $fromDate);
        $query->where('date', '<=', $toDate);
        $query->select(DB::raw('client_id, type, date, DATE_FORMAT(date,"%Y") as year,
                DATE_FORMAT(date,"%m-%Y") as month,sum(amount) as amount_exclude_refund,
                sum(number_trans) as number_trans_exclude_refund,
                sum(amount) as amount,
                sum(number_trans) as number_trans,
                sum(commission_bank_fee) as commission_bank_fee,
                system_usage_rate,
                account_balance,
                account_number'));

        $query->groupBy(['client_id', 'date', 'type']);
        $query->orderBy('date', 'desc');

        $query->leftJoin(
            DB::raw("(select client_id as c_id, system_usage_rate from client) as client"),
            'client_aggregation.client_id', '=', 'client.c_id');

        if ($request->csv) {
            $query->reorder('client_id', 'asc')->orderBy('date', 'desc');
            $query->groupBy(['account_number']);
        }

        return $query->get();
    }

    public function getTransaction(Request $request): Collection
    {
        $query = $this->getQuery();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->page) {
            $fromDate = $this->getFromDateByPage($request->page);
            $toDate = $this->getToDateByPage($request->page);
        } else {
            $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }

        $query->select(DB::raw('client_id, type, date, DATE_FORMAT(date,"%Y") as year,
                represent_name,
                DATE_FORMAT(date,"%m-%Y") as month,sum(amount) as amount_exclude_refund,
                sum(number_trans) as number_trans_exclude_refund,
                sum(amount) as amount,
                sum(number_trans) as number_trans,
                sum(commission_bank_fee) as commission_bank_fee,
                system_usage_rate,
                account_balance,
                account_number'));

        if ($request->group_by_date) {
            $query->where('date', '>=', $fromDate);
            $query->where('date', '<=', $toDate);
            if ($request->is_sum) {
                $query->groupBy(['client_id', 'type']);
            } else {
                $query->groupBy(['client_id', 'date', 'type']);
            }
            $query->orderBy('date', 'desc');
        } elseif ($request->group_by_month) {
            $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
            $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
            $query->where('date', '>=', $fromDate);
            $query->where('date', '<=', $toDate);

            if ($request->is_sum) {
                $query->groupBy(['client_id','month','type']);
            }else {
                $query->groupBy(['client_id','date','type']);
            }
            $query->orderBy('date', 'desc');
        } else {
            $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfYear()->format('Y-m-d H:i:s');
            $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfYear()->format('Y-m-d H:i:s');
            $query->where('date', '>=', $fromDate);
            $query->where('date', '<=', $toDate);
            if ($request->is_sum) {
                $query->groupBy(['client_id','year','type']);
            }else {
                $query->groupBy(['client_id','month','type']);
            }
            $query->orderBy('date', 'desc');
        }

        $query->leftJoin(
            DB::raw("(select client_id as c_id, represent_name, system_usage_rate from client) as client"),
            'client_aggregation.client_id', '=', 'client.c_id');

        if ($request->csv) {
            $query->reorder('client_id', 'asc')->orderBy('date', 'desc');
            $query->groupBy(['account_number']);
        }

        return $query->get();
    }

    public function getAccountUsageFee (Request $request): Collection
    {
        $query = $this->getQuery();

        if ($request->client_id) {
            $query->where('client_id', $request->client_id);
        }
        $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
        $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->endOfMonth()->format('Y-m-d H:i:s');
        $query->where('date', '>=', $fromDate);
        $query->where('date', '<=', $toDate);
        $query->where('client_aggregation.account_number', '!=', '');
        $query->where('client_aggregation.account_number', 'not like', '%account%');
        $query->where('client_aggregation.account_number', 'not like', '%deposit%');
        if ($request->is_account) {
            $query->select(DB::raw('client_id, type,
                date,sum(amount) as amount_exclude_refund,
                sum(number_trans) as number_trans_exclude_refund,
                sum(amount) - COALESCE(refund.refund_amount_charge, 0) as amount,
                sum(number_trans) - COALESCE(refund.number_refunds_charge, 0) as number_trans,
                COALESCE(refund.number_refunds_charge, 0) as number_refunds,
                COALESCE(refund.refund_amount_charge, 0) as refund_amount,
                COALESCE(refund_amount_charge_fee, 0) as refund_fee, account_balance'));
            $query->leftJoin(
                DB::raw('(select type_client_aggregation,count(*) as number_refunds_charge,
                client_id as charge_c_id,
                account_number,
                DATE_FORMAT(create_date,"%Y-%m-%d") as create_date_join,
                COALESCE(sum(charge_fee), 0) as refund_amount_charge_fee,
                COALESCE(sum(payment_amount), 0) as refund_amount_charge  from charge_history
                where type = '. ChargeHistory::REFUND.' and create_date >= "'.$fromDate.'" and create_date <= "'.$toDate.'" group by charge_c_id,create_date_join, type_client_aggregation) as refund'),
                function ($join){
                    $join->on('client_aggregation.type', '=', 'refund.type_client_aggregation');
                    $join->on(DB::raw('DATE_FORMAT(date,"%Y-%m-%d")'),'=','refund.create_date_join');
                    $join->on('client_aggregation.client_id','=','refund.charge_c_id');
                });
            $query->groupBy(['client_id','type']);

        } else {
            $query->select(DB::raw('client_id, type, date,
                sum(amount) as amount_exclude_refund,
                sum(number_trans) as number_trans_exclude_refund,
                sum(amount) - COALESCE(sum(refund.refund_amount_charge), 0) + COALESCE(sum(dewi.dewi_amount_charge), 0) as amount,
                sum(number_trans) - COALESCE(sum(refund.number_refunds_charge), 0) + COALESCE(sum(dewi.number_dewi_charge), 0) as number_trans,
                FLOOR((sum(amount) - COALESCE(sum(refund.refund_amount_charge), 0) + COALESCE(sum(dewi.dewi_amount_charge), 0)) * COALESCE(client_aggregation.account_usage_rate, 0) / 100) as account_usage_fee,
                COALESCE(sum(refund.number_refunds_charge), 0) as number_refunds,
                COALESCE(sum(refund.refund_amount_charge), 0) as refund_amount,
                COALESCE(refund_amount_charge_fee, 0) as refund_fee,
                COALESCE(client_aggregation.account_usage_rate, 0) as account_usage_rate,
                client_aggregation.account_number,commission_rate, account_holder, bank_name,company_name, account_balance'));
            $query->leftJoin(
                DB::raw('(select type_client_aggregation,count(*) as number_refunds_charge,
                client_id as charge_c_id,
                account_number,
                DATE_FORMAT(create_date,"%Y-%m-%d") as create_date_join,
                COALESCE(sum(charge_fee), 0) as refund_amount_charge_fee,
                COALESCE(sum(payment_amount), 0) as refund_amount_charge  from charge_history
                where type = ' . ChargeHistory::REFUND . ' and create_date >= "' . $fromDate . '" and create_date <= "' . $toDate . '" group by charge_c_id,create_date_join, type_client_aggregation) as refund'),
                function ($join) {
                    $join->on('client_aggregation.type', '=', 'refund.type_client_aggregation');
                    $join->on(DB::raw('DATE_FORMAT(date,"%Y-%m-%d")'), '=', 'refund.create_date_join');
                    $join->on('client_aggregation.client_id', '=', 'refund.charge_c_id');
                    $join->on('client_aggregation.account_number', '=', 'refund.account_number');
                });
            $query->leftJoin(
                DB::raw('(select type_client_aggregation,count(*) as number_dewi_charge,
                client_id as charge_c_id,
                account_number,
                DATE_FORMAT(create_date,"%Y-%m-%d") as create_date_join,
                COALESCE(sum(charge_fee), 0) as dewi_amount_charge_fee,
                COALESCE(sum(payment_amount), 0) as dewi_amount_charge  from charge_history
                where type = ' . ChargeHistory::DEPOSIT_WITHDRAWAL . ' and create_date >= "' . $fromDate . '" and create_date <= "' . $toDate . '" group by charge_c_id,create_date_join, type_client_aggregation) as dewi'),
                function ($join) {
                    $join->on('client_aggregation.type', '=', 'dewi.type_client_aggregation');
                    $join->on(DB::raw('DATE_FORMAT(date,"%Y-%m-%d")'), '=', 'dewi.create_date_join');
                    $join->on('client_aggregation.client_id', '=', 'dewi.charge_c_id');
                    $join->on('client_aggregation.account_number', '=', 'dewi.account_number');
                });
            $query->leftJoin(
                DB::raw("(select account_number, account_holder, bank_name,contractor_id, commission_rate from account) as account"),
                'client_aggregation.account_number', '=', 'account.account_number');

            $query->leftJoin(
                DB::raw("(select id, company_name from contructor) as contructor"),
                'contructor.id', '=', 'account.contractor_id');

            $query->groupBy(['client_id', 'type', 'client_aggregation.account_number', 'client_aggregation.account_usage_rate']);

            if ($request->contractor_id) {
                $accountListOfContractor = Account::withTrashed()->where('contractor_id', '=', $request->contractor_id)->get();
                //truong hop contractor khong con account nao
                if ($accountListOfContractor->isEmpty()) {
                    $query->where('client_aggregation.account_number', '=', 'not_account_select');
                }
                $query->where(function ($query) use ($accountListOfContractor) {
                    foreach ($accountListOfContractor as $account) {
                        $query->orWhere('client_aggregation.account_number', '=', $account->account_number);
                    }
                });
            }
        }

        return $query->get();
    }

    public function getRequestAllClientToday()
    {
        $date = Carbon::now()->startOfDay();
        $requests = DB::connection('remote')->table('requests')
            ->select(DB::raw('SUM(am_deposited) AS amount, COUNT(*) as number_of_deposit, user_id'))
            ->where('date_deposited', '>=', $date)
            ->where(function ($query) {
                $query->where('status', '=', 'completed')
                    ->orWhere('status', '=', 'on hold');
            });
        return $requests->groupBy('user_id')->get();
    }

    public function getWithdrawalAllClientToday($isAccount = false)
    {
        $date = Carbon::now()->startOfDay();
        $withdrawals = DB::connection('remote')->table('withdrawals')
            ->where('date_completed', '>=', $date)->where('status', '=', 'completed');
        if ($isAccount) {
            $withdrawals->select(DB::raw('SUM(amount) AS amount, COUNT(*) as number_of_withdrawal, user_id, from_account'));
            return $withdrawals->groupBy('user_id', 'from_account')->get();
        } else {
            $withdrawals->select(DB::raw('SUM(amount) AS amount, COUNT(*) as number_of_withdrawal, user_id'));
        }
        return $withdrawals->groupBy('user_id')->get();
    }

    public function getSummaryClientAggregation(Request $request)
    {
        $date = Carbon::now()->startOfDay();
        $requests = DB::connection('remote')->table('requests')
            ->select(DB::raw('SUM(am_deposited) AS amount, COUNT(*) as number_of_deposit'))
            ->where('date_deposited', '>=', $date)
            ->where(function ($query) {
                $query->where('status', '=', 'completed')
                    ->orWhere('status', '=', 'on hold');
            });
        $withdrawals = DB::connection('remote')->table('withdrawals')
            ->select(DB::raw('SUM(amount) AS amount, COUNT(*) as number_of_withdrawal'))
            ->where('date_completed', '>=', $date)->where('status', '=', 'completed');

        $unknowDeposit = DB::connection('remote')->table('scheduled_scrappers')
            ->select(DB::raw('SUM(amount) AS amount, COUNT(*) as number_of_scheduled_scrappers'))
            ->where('is_matched', 0);

        $totalDeposit = 0;
        $totalWithdrawal = 0;

        if ($request->client_id) {
            $withdrawals->where('user_id', '=', $request->client_id);
            $requests->where('user_id', '=', $request->client_id);
            $unknowDeposit->rightJoin(
                DB::raw("(select id,user_id from merchants where user_id = " . $request->client_id . ") as merchant"),
                'scheduled_scrappers.merchant_id', '=', 'merchant.id');


            $depositBalanceYesterday = Account::where(function ($query) use ($request) {
                $query->where('client_id', '=', $request->client_id);
                $query->where('service_type', '=', 1);
            });

            $depositBalanceYesterday = $depositBalanceYesterday->orWhere(function ($query) use ($request) {
                $query->where('client_id', '=', $request->client_id);
                $query->where('service_type', '=', 3);
            })->get();

            foreach ($depositBalanceYesterday as $account) {
                $balance = AccountBalanceHistory::where('account_number', '=', $account->account_number)
                    ->whereDate('date_history', '<', Carbon::now())->orderBy('date_history', 'desc')->first();
                $totalDeposit += $balance ? $balance->balance : 0;
            }

            $withdrawalsYesterday = Account::where(function($query) use ($request) {
                $query->where('client_id', '=', $request->client_id);
                $query->where('service_type', '=', 2);
            })->get();

            foreach ($withdrawalsYesterday as $account) {
                $balance = AccountBalanceHistory::where('account_number', '=', $account->account_number)->orderBy('date_history', 'desc')->first();
                $totalWithdrawal += $balance ? $balance->balance : 0;
            }
        }
        $withdrawals = $withdrawals->get();
        $requests = $requests->get();
        $unknowDeposit = $unknowDeposit->get();

        return [
            'deposit' => $requests,
            'withdrawls' => $withdrawals,
            'unknow' => $unknowDeposit,
            'deposit_balance' => $totalDeposit + $requests->first()->amount ?? 0,
            'withdrawals_balance' => $totalWithdrawal - $withdrawals->first()->amount ?? 0
        ];
    }

    public function getTransactionByAccountNumberInDate($clientId, $dateHistory, $accountNumber)
    {
        return $this->clearQuery()
            ->where('date', '=', $dateHistory)
            ->where('client_id', '=', $clientId)
            ->where('account_number', '=', $accountNumber)
            ->first();
    }

    public function getInComeAndExpenditure(Request $request): Collection
    {
        $query = $this->clearQuery();
        $fromDate = Carbon::parse($request->from_date)->startOfDay()->format('Y-m-d H:i:s');
        $toDate = Carbon::parse($request->to_date)->endOfDay()->format('Y-m-d H:i:s');

        if ($request->group_by_year) {
            $fromDate = Carbon::parse($request->from_date)->startOfYear()->format('Y-m-d H:i:s');
            $toDate = Carbon::parse($request->to_date)->endOfYear()->format('Y-m-d H:i:s');
        }

        if ($request->group_by_month) {
            $fromDate = Carbon::parse($request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
            $toDate = Carbon::parse($request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
        }

        $query->where('date', '>=', $fromDate);
        $query->where('date', '<=', $toDate);
        $query->select(DB::raw('client_id, type, date,
                DATE_FORMAT(date,"%m-%Y") as month,
                DATE_FORMAT(date,"%Y") as year,
                represent_name,
                sum(amount) as amount,
                sum(number_trans) as number_trans,
                sum(account_fee) as account_fee,
                sum(commission_bank_fee) as commission_bank_fee,
                sum(transfer_fee_different) as transfer_fee_different,
                system_usage_rate,
                contractor_id,
                client_aggregation.account_number as account_number,
                account_balance'));
        $query->leftJoin(
            DB::raw("(select client_id as c_id, system_usage_rate, represent_name from client) as client"),
            'client_aggregation.client_id', '=' , 'client.c_id');
        $query->leftJoin(
            DB::raw("(select account_number, account_holder, bank_name,contractor_id, commission_rate, service_type from account) as account"),
            'client_aggregation.account_number', '=', 'account.account_number');

        $query->groupBy(['client_id', 'date', 'type', 'account_number']);
        $query->orderBy('client_id', 'asc');
        $query->orderBy('date', 'desc');



        return $query->get();
    }

    public function getSummaryIncomeExpenditure(Request $request) {
        $query = $this->getQuery();

        $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
        $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->endOfMonth()->format('Y-m-d H:i:s');
        $query->where('date', '>=', $fromDate);
        $query->where('date', '<=', $toDate);
//        $query->where('client_aggregation.account_number', '!=', '');
//        $query->where('client_aggregation.account_number', 'not like', '%account%');
//        $query->where('client_aggregation.account_number', 'not like', '%deposit%');
        $query->select(DB::raw('client_id, type, date,
                DATE_FORMAT(date,"%Y-%m-%d") as date_compare,
                sum(amount) as amount_exclude_refund,
                sum(number_trans) as number_trans_exclude_refund,
                sum(amount) - COALESCE(sum(refund.refund_amount_charge), 0) + COALESCE(sum(dewi.dewi_amount_charge), 0) as amount,
                sum(number_trans) - COALESCE(sum(refund.number_refunds_charge), 0) + COALESCE(sum(dewi.number_dewi_charge), 0) as number_trans,
                (sum(amount) - COALESCE(sum(refund.refund_amount_charge), 0) + COALESCE(sum(dewi.dewi_amount_charge), 0)) * COALESCE(client_aggregation.account_usage_rate, 0) / 100 as account_usage_fee,
                COALESCE(sum(refund.number_refunds_charge), 0) as number_refunds,
                COALESCE(sum(refund.refund_amount_charge), 0) as refund_amount,
                COALESCE(refund_amount_charge_fee, 0) as refund_fee,
                COALESCE(client_aggregation.account_usage_rate, 0) as account_usage_rate,represent_name, system_usage_rate,contractor_id,
                client_aggregation.account_number,commission_rate, account_holder, bank_name, service_type'));
        $query->leftJoin(
            DB::raw('(select type_client_aggregation,count(*) as number_refunds_charge,
                client_id as charge_c_id,
                account_number,
                DATE_FORMAT(create_date,"%Y-%m-%d") as create_date_join,
                COALESCE(sum(charge_fee), 0) as refund_amount_charge_fee,
                COALESCE(sum(payment_amount), 0) as refund_amount_charge  from charge_history
                where type = ' . ChargeHistory::REFUND . ' and create_date >= "' . $fromDate . '" and create_date <= "' . $toDate . '" group by charge_c_id,create_date_join, type_client_aggregation) as refund'),
            function ($join) {
                $join->on('client_aggregation.type', '=', 'refund.type_client_aggregation');
                $join->on(DB::raw('DATE_FORMAT(date,"%Y-%m-%d")'), '=', 'refund.create_date_join');
                $join->on('client_aggregation.client_id', '=', 'refund.charge_c_id');
                $join->on('client_aggregation.account_number', '=', 'refund.account_number');
            });
        $query->leftJoin(
            DB::raw('(select type_client_aggregation,count(*) as number_dewi_charge,
                client_id as charge_c_id,
                account_number,
                DATE_FORMAT(create_date,"%Y-%m-%d") as create_date_join,
                COALESCE(sum(charge_fee), 0) as dewi_amount_charge_fee,
                COALESCE(sum(payment_amount), 0) as dewi_amount_charge  from charge_history
                where type = ' . ChargeHistory::DEPOSIT_WITHDRAWAL . ' and create_date >= "' . $fromDate . '" and create_date <= "' . $toDate . '" group by charge_c_id,create_date_join, type_client_aggregation) as dewi'),
            function ($join) {
                $join->on('client_aggregation.type', '=', 'dewi.type_client_aggregation');
                $join->on(DB::raw('DATE_FORMAT(date,"%Y-%m-%d")'), '=', 'dewi.create_date_join');
                $join->on('client_aggregation.client_id', '=', 'dewi.charge_c_id');
                $join->on('client_aggregation.account_number', '=', 'dewi.account_number');
            });
        $query->leftJoin(
            DB::raw("(select account_number, account_holder, bank_name,contractor_id, commission_rate, service_type from account) as account"),
            'client_aggregation.account_number', '=', 'account.account_number');
        $query->leftJoin(
            DB::raw("(select client_id as c_id,represent_name, system_usage_rate from client) as client"),
            'client_aggregation.client_id', '=', 'client.c_id');

        $query->groupBy(['client_id', 'type', 'client_aggregation.account_number', 'client_aggregation.account_usage_rate']);
        $query->orderBy('client_id');

        return $query->get();
    }

    public function createClientAggregationByAccountNumber($data)
    {
        if (Arr::exists($data, 'account_number')) {
            if ($data['account_number']) {
                $cg = ClientAggregation::where('client_id', $data['client_id'])
                    ->where('account_number', $data['account_number'])
                    ->where('date', '>=', Carbon::parse($data['create_date'])->startOfDay()->format('Y-m-d H:i:s'))
                    ->where('date', '<=', Carbon::parse($data['create_date'])->endOfDay()->format('Y-m-d H:i:s'))
                    ->first();
                if (!$cg) {
                    $cgRate = ClientAggregation::where('client_id', $data['client_id'])
                        ->where('account_number', $data['account_number'])
                        ->where('date', '<=', Carbon::parse($data['create_date'])->endOfDay()->format('Y-m-d H:i:s'))
                        ->orderBy('date', 'desc')
                        ->first();

                    if ($cgRate) {
                        $rate = $cgRate->account_usage_rate;
                    } else {
                        $account = Account::where('account_number', $data['account_number'])->first();
                        $rate = $account ? $account->commission_rate : 0;
                    }

                    if ($data['type_client_aggregation'] > 0) {
                        ClientAggregation::insert([
                            'client_id' => $data['client_id'],
                            'amount' => 0,
                            'number_trans' => 0,
                            'commission_bank_fee' => 0,
                            'display_amount' => 0,
                            'date' => Carbon::parse($data['create_date'])->startOfDay()->format('Y-m-d H:i:s'),
                            'account_number' => $data['account_number'],
                            "account_usage_rate" => $rate,
                            "account_fee" => 0,
                            'type' => $data['type_client_aggregation'],
                            'user_edit_id' => 0,
                            'type_refund' => 0,
                            'memo' => '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

}
