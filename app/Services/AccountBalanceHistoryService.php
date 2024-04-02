<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountBalanceHistory;
use App\Models\ChargeHistory;
use App\Models\ClientAggregation;
use App\Repositories\Interfaces\AccountBalanceHistoryRepositoryInterface;
use App\Repositories\Interfaces\ChargeHistoryRepositoryInterface;
use App\Repositories\Interfaces\ClientAggregationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountBalanceHistoryService
{
    public AccountBalanceHistoryRepositoryInterface $accountBalanceHistoryRepository;
    public ChargeHistoryRepositoryInterface $chargeHistoryRepository;
    public ClientAggregationRepositoryInterface $clientAggregationRepository;

    public function __construct(
        AccountBalanceHistoryRepositoryInterface $accountBalanceHistoryRepository,
        ChargeHistoryRepositoryInterface         $chargeHistoryRepository,
        ClientAggregationRepositoryInterface     $clientAggregationRepository
    )
    {
        $this->accountBalanceHistoryRepository = $accountBalanceHistoryRepository;
        $this->chargeHistoryRepository = $chargeHistoryRepository;
        $this->clientAggregationRepository = $clientAggregationRepository;
    }

    public function store($data)
    {
        return $this->accountBalanceHistoryRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->accountBalanceHistoryRepository->update($id, $request->all());
    }

    public function getList($request)
    {
        return $this->accountBalanceHistoryRepository->getList($request);
    }

    public function reCalculateAccountBalance($clientId, $accountNumber, $dateHistory, $amount = 0, $type = 0)
    {
        $fromDate = Carbon::parse($dateHistory)->startOfDay()->format('Y-m-d H:i:s');
        $toDate = Carbon::parse($dateHistory)->endOfDay()->format('Y-m-d H:i:s');

        $chargeHistory = $this->chargeHistoryRepository->getChargeToReCalculateBalance($clientId, $fromDate, $toDate, $accountNumber);
        if ($type == 1) {
            $transfer = $this->chargeHistoryRepository->getChargeTransferToReCalculateBalance($clientId,$fromDate,$toDate);
            $chargeHistory = $chargeHistory->merge($transfer);
        }
        if (!$amount) {
            $clientAggregation = $this->clientAggregationRepository->getTransactionByAccountNumberInDate($clientId, $fromDate, $accountNumber);
            $amount = $clientAggregation ? $clientAggregation->amount : 0;
            $type = $clientAggregation ? $clientAggregation->type : 0;
        }

        $accountBalance = AccountBalanceHistory::firstOrNew([
            'client_id' => $clientId,
            'date_history' => $fromDate,
            'account_number' => $accountNumber
        ]);

        $beforeBalance = $accountBalance->balance ?? 0;

        $lastAccountBalance = AccountBalanceHistory::where('date_history', '<', $fromDate)
            ->where('client_id', '=', $clientId)
            ->where('account_number', '=', $accountNumber)
            ->orderBy('date_history', 'desc')->first();
        $lastBalance = 0;
        if ($lastAccountBalance) {
            $lastBalance = $lastAccountBalance->balance;
        }

        if ($type == 1) {
            $accountBalance->balance = $amount + $lastBalance;
        } else {
            $accountBalance->balance = $lastBalance - $amount;
        }

        foreach ($chargeHistory as $item) {
            switch ($item->type) {
                case ChargeHistory::SETTLEMENT:
                    $accountBalance->balance = $accountBalance->balance - $item->payment_amount - $item->charge_fee;
                    break;
                case ChargeHistory::REFUND:
                    if ($type == 1) {
                        $accountBalance->balance = $accountBalance->balance - $item->payment_amount - $item->charge_fee;
                    } else {
                        $accountBalance->balance = $accountBalance->balance + $item->payment_amount - $item->charge_fee;
                    }
                    break;
                case ChargeHistory::TRANSFER:
                    if ($type == 1) {
                        $accountBalance->balance = $accountBalance->balance - $item->payment_amount;
                    } else {
                        $accountBalance->balance = $accountBalance->balance + $item->payment_amount;
                    }
                    break;
                case ChargeHistory::CHARGE:
                    $accountBalance->balance = $accountBalance->balance + $item->payment_amount - $item->charge_fee;
                    break;
                case ChargeHistory::DEPOSIT_WITHDRAWAL:
                    if ($type == 1) {
                        $accountBalance->balance = $accountBalance->balance + $item->payment_amount;
                    } else {
                        $accountBalance->balance = $accountBalance->balance - $item->payment_amount - $item->charge_fee;
                    }
                    break;
            }
        }
        $afterBalance = $accountBalance->balance ?? 0;
        $accountBalance->save();

        // Check Tinh toan lai so du neu so du hien tai thay doi
        $balanceChange = $afterBalance - $beforeBalance;
        if ($balanceChange) {
            $listAccountBalance = AccountBalanceHistory::where('date_history', '>', $accountBalance->date_history)
                ->where('client_id', '=', $clientId)
                ->where('account_number', '=', $accountNumber)
                ->orderBy('date_history', 'asc')->get();
            foreach ($listAccountBalance as $history) {
                $history->balance = $history->balance + $balanceChange;
                $history->save();
            }
        }
    }

}
