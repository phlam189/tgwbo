<?php

namespace App\Repositories\Interfaces;


interface ChargeHistoryRepositoryInterface extends RepositoryInterface
{
    public function getList($request, $fromDate, $toDate);

    public function getByTypeAggregation($clientId, $fromDate, $toDate, $typeAggregation);

    public function getByAccountNumber($clientId, $fromDate, $toDate, $accountNumber);

    public function getChargeToReCalculateBalance($clientId, $fromDate, $toDate, $accountNumber);

    public function getChargeTransferToReCalculateBalance($clientId, $fromDate, $toDate);
    public function getBorrowingByClient($clientId, $date, $type);
    public function getRepaymentByClient($clientId, $date, $type);
    public function getInterestByClient($clientId, $date, $type);
    public function getMiscByClient($clientId, $date, $type);
    public function getTransferByClient($clientId, $date, $type);
    public function getListIncome(\Illuminate\Http\Request $request);
    public function getListIncomeRefundDewi(\Illuminate\Http\Request $request);
}
