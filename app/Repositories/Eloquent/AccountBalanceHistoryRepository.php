<?php

namespace App\Repositories\Eloquent;

use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Repositories\Interfaces\AccountBalanceHistoryRepositoryInterface;
use Illuminate\Support\Carbon;

class AccountBalanceHistoryRepository extends BaseRepository implements AccountBalanceHistoryRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return AccountBalanceHistory::class;
    }

    public function getAccountBalanceByDate($clientId, $accountNumber, $toDate, $type)
    {
        $toDate = Carbon::parse($toDate)->endOfDay()->format('Y-m-d H:i:s');
        if ($type == 1) {
            $accountDeposit = Account::where('client_id', $clientId)->where('service_type', 1)->first();
            $accountNumber = $accountDeposit ? $accountDeposit->account_number : 'not-found';
        }
        return $this->clearQuery()->where('account_balance_history.client_id', '=', $clientId)
            ->select('balance')
            ->where('account_number', '=', $accountNumber)
            ->where('date_history', '<=', $toDate)
            ->orderByDesc('date_history')->first();
    }

    public function getList($request)
    {
        return $this->model->where('account_number', $request->account_number)->orderByDesc('date_history')->first();
    }
}
