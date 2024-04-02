<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface ClientAggregationRepositoryInterface extends RepositoryInterface
{
    public function getTransaction(Request $request): Collection;

    public function getSummaryClientAggregation(Request $request);

    public function getClientAggregationByClientId($clientId, $fromDate = null);

    public function getAccountUsageFee(Request $request): Collection;

    public function getInComeAndExpenditure(Request $request): Collection;

    public function getTransactionByDate(Request $request);

    public function getTransactionByAccountNumberInDate($clientId, $dateHistory, $accountNumber);

    public function getRequestAllClientToday();

    public function getWithdrawalAllClientToday($isAccount = false);

    public function getSummaryIncomeExpenditure(Request $request);

    public function createClientAggregationByAccountNumber($data);
}
