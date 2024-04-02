<?php

namespace App\Repositories\Eloquent;

use App\Models\ChargeHistory;
use App\Repositories\Interfaces\ChargeHistoryRepositoryInterface;
use Illuminate\Support\Carbon;

class ChargeHistoryRepository extends BaseRepository implements ChargeHistoryRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return ChargeHistory::class;
    }

    public function getList($request, $fromDate, $toDate)
    {
        $chargeHistory = ChargeHistory::getQuery();
        if ($request->client_id) {
            $chargeHistory->where('client_id', $request['client_id']);
        }
        $chargeHistory = $chargeHistory->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate);

        if ($request->loan) {
            return $chargeHistory->where(function ($query) {
                $query->where('type', '=', ChargeHistory::BORROWING)
                    ->orWhere('type', '=', ChargeHistory::REPAYMENT)
                    ->orWhere(function ($query) {
                        $query->where('type', '=', ChargeHistory::DEPOSIT_WITHDRAWAL);
                        $query->where('type_client_aggregation', '=', 2);
                    });

            })->select([
                'charge_history.id',
                'charge_history.client_id',
                'charge_history.type_client_aggregation',
                'charge_history.type',
                'charge_history.memo',
                'charge_history.memo_internal',
                'charge_history.payment_amount',
                'charge_history.charge_fee',
                'charge_history.create_date',
                'client.represent_name',
                'charge_history.account_number',
                'account.account_holder',
                'account.bank_name',
                'account.branch_name',

            ])
                ->leftJoin('account', 'account.account_number', 'charge_history.account_number')
                ->leftJoin('client', 'client.client_id', 'charge_history.client_id')
                ->orderBy('create_date', 'desc')
                ->orderBy('client_id', 'asc')
                ->orderBy('type_client_aggregation', 'asc')
                ->orderBy('type', 'asc')
                ->get();
        }
        if (!$request->all_type) {
            return $chargeHistory->where(function ($query) {
                $query->where('type', '=', ChargeHistory::SETTLEMENT)
                    ->orWhere('type', '=', ChargeHistory::CHARGE);
            })
                ->paginate(env('PAGINATE_DEFAULT', 30));
        }
        return $chargeHistory->get();
    }

    public function getByAccountNumber($clientId, $fromDate, $toDate, $accountNumber)
    {
        $query = ChargeHistory::where('client_id', $clientId)
            ->where('account_number', $accountNumber)
            ->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate);

        return $query->get();
    }

    public function getByTypeAggregation($clientId, $fromDate, $toDate, $typeAggregation)
    {
        $query = ChargeHistory::where('client_id', $clientId)
            ->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate);
        $query->where(function ($query) use ($typeAggregation) {
            $query->where('type_client_aggregation', '=', $typeAggregation);
            $query->orWhere('type_client_aggregation', '=', 0);
        });

        return $query->get();
    }

    public function getChargeToReCalculateBalance($clientId, $fromDate, $toDate, $accountNumber)
    {
        return ChargeHistory::where('client_id', $clientId)
            ->where('account_number', '=', $accountNumber)
            ->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate)
            ->get();
    }

    public function getChargeTransferToReCalculateBalance($clientId, $fromDate, $toDate)
    {
        return ChargeHistory::where('client_id', $clientId)
            ->where('type', '=', ChargeHistory::TRANSFER)
            ->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate)
            ->get();
    }

    public function getBorrowingByClient($clientId, $date, $type) {
        return ChargeHistory::where('client_id', $clientId)
            ->where('type', '=', ChargeHistory::BORROWING)
            ->where('type_client_aggregation', '=', $type)
            ->where('create_date', '<=', $date)
            ->get();
    }

    public function getRepaymentByClient($clientId, $date, $type) {
        return ChargeHistory::where('client_id', $clientId)
            ->where('type', '=', ChargeHistory::REPAYMENT)
            ->where('type_client_aggregation', '=', $type)
            ->where('create_date', '<=', $date)
            ->get();
    }

    public function getInterestByClient($clientId, $date, $type) {
        return ChargeHistory::where('client_id', $clientId)
            ->where('type', '=', ChargeHistory::INTEREST)
            ->where('type_client_aggregation', '=', $type)
            ->where('create_date', '<=', $date)
            ->get();
    }

    public function getMiscByClient($clientId, $date, $type) {
        return ChargeHistory::where('client_id', $clientId)
            ->where('type', '=', ChargeHistory::MISC)
            ->where('type_client_aggregation', '=', $type)
            ->where('create_date', '<=', $date)
            ->get();
    }

    public function getTransferByClient($clientId, $date, $type) {
        return ChargeHistory::where('client_id', $clientId)
            ->where('type', '=', ChargeHistory::TRANSFER)
            ->where('create_date', '<=', $date)
            ->get();
    }


    public function getListIncomeRefundDewi(\Illuminate\Http\Request $request)
    {
        $chargeHistory = ChargeHistory::getQuery();
        $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
        $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
        $chargeHistory = $chargeHistory->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate);

        return $chargeHistory->where(function ($query) {
            $query->where('type', '=', ChargeHistory::REFUND)
                ->orWhere('type', '=', ChargeHistory::DEPOSIT_WITHDRAWAL);

        })->select([
            'charge_history.id',
            'charge_history.client_id',
            'charge_history.type_client_aggregation',
            'charge_history.type',
            'charge_history.memo',
            'charge_history.memo_internal',
            'charge_history.payment_amount',
            'charge_history.charge_fee',
            'charge_history.create_date',
            'client.represent_name',
            'client.system_usage_rate',
            'client.charge_fee_rate',
            'client.settlement_fee_rate'

        ])
            ->leftJoin('client', 'client.client_id', 'charge_history.client_id')
            ->orderBy('client_id', 'desc')
            ->get();
    }

    /**
     * Settlement fee, deposit charge fee, remittance fee difference, and interest are miscellaneous income
     * @param $request
     * @param $fromDate
     * @param $toDate
     * @return mixed
     */

    public function getListIncome(\Illuminate\Http\Request $request)
    {
        $chargeHistory = ChargeHistory::getQuery();
        $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
        $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
        $chargeHistory = $chargeHistory->where('create_date', '>=', $fromDate)
            ->where('create_date', '<=', $toDate);

        return $chargeHistory->where(function ($query) {
            $query->where('type', '=', ChargeHistory::SETTLEMENT)
                ->orWhere('type', '=', ChargeHistory::CHARGE)
                ->orWhere('type', '=', ChargeHistory::INTEREST);

        })->select([
            'charge_history.id',
            'charge_history.client_id',
            'charge_history.type_client_aggregation',
            'charge_history.type',
            'charge_history.memo',
            'charge_history.memo_internal',
            'charge_history.payment_amount',
            'charge_history.charge_fee',
            'charge_history.create_date',
            'client.represent_name',
            'client.system_usage_rate',
            'client.charge_fee_rate',
            'client.settlement_fee_rate'

        ])
            ->leftJoin('client', 'client.client_id', 'charge_history.client_id')
            ->orderBy('client_id', 'desc')
            ->get();
    }

}
