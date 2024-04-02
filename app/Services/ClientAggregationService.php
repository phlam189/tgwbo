<?php

namespace App\Services;

use App\Models\Account;
use App\Exports\InvoicesExport;
use App\Models\Bank;
use App\Models\ChargeHistory;
use App\Models\Client;
use App\Models\ClientContractDetail;
use App\Models\IncomeExpenditure;
use App\Models\IncomeExpenditureDetail;
use App\Models\IntroducerInformation;
use App\Models\Invoice;
use App\Repositories\Interfaces\AccountBalanceHistoryRepositoryInterface;
use App\Repositories\Interfaces\ChargeHistoryRepositoryInterface;
use App\Repositories\Interfaces\ClientAggregationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ClientAggregationService
{
    public ClientAggregationRepositoryInterface $clientAggregationRepository;
    public ChargeHistoryRepositoryInterface $chargeHistoryRepository;
    public AccountBalanceHistoryRepositoryInterface $accountBalanceHistoryRepository;

    public function __construct(
        ClientAggregationRepositoryInterface     $clientAggregationRepository,
        ChargeHistoryRepositoryInterface         $chargeHistoryRepository,
        AccountBalanceHistoryRepositoryInterface $accountBalanceHistoryRepository
    ) {
        $this->clientAggregationRepository = $clientAggregationRepository;
        $this->chargeHistoryRepository = $chargeHistoryRepository;
        $this->accountBalanceHistoryRepository = $accountBalanceHistoryRepository;
    }

    public function getTransaction(Request $request): Collection
    {
        return $this->clientAggregationRepository->getTransaction($request);
    }

    public function getTransactionByDate(Request $request): Collection
    {
        return $this->clientAggregationRepository->getTransactionByDate($request);
    }

    public function getAccountUsageFee(Request $request): Collection
    {
        return $this->clientAggregationRepository->getAccountUsageFee($request);
    }

    public function aggregationByClient($transactionList, $request, $dataToday = [])
    {
        $now = Carbon::now();

        $aggregationByClient = [];
        $aggregationByClient['balance'] = 0;
        $aggregationByClient['number_trans'] = 0;
        $aggregationByClient['amount'] = 0;
        $aggregationByClient['system_usage_fee'] = 0;
        $aggregationByClient['account_fee'] = 0;
        foreach ($transactionList as $key => $transactionByClient) {
            if ($request->group_by_date) {
                $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
            } else {
                $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
            }

            $accounts = Account::where('client_id', '=', $key)
                ->where('service_type', '=', $transactionByClient->first()->type)->get();
            $totalBalance = 0;
            foreach ($accounts as $account) {
                $accountBalance = $this->accountBalanceHistoryRepository->getAccountBalanceByDate($key, $account->account_number, $toDate, $transactionByClient->first()->type);
                if ($accountBalance) {
                    $totalBalance += $accountBalance->balance;
                }
            }

            $aggregationByClient['balance'] += $totalBalance;
            $aggregationByClient['number_trans'] += $transactionByClient->sum('number_trans');
            $aggregationByClient['amount'] += $transactionByClient->sum('amount');
            $aggregationByClient['system_usage_fee'] += $transactionByClient->sum('system_usage_fee');
            $aggregationByClient['account_fee'] += $transactionByClient->sum('account_fee');
            if (Arr::exists($dataToday,'deposit_balance')
                && $transactionByClient->first()->type == 1
                && Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->isSameMonth(Carbon::now())) {
                $aggregationByClient['balance'] = $dataToday['deposit_balance'];
            }

            if (Arr::exists($dataToday,'withdrawals_balance')
                && $transactionByClient->first()->type == 2
                && Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->isSameMonth(Carbon::now())) {
                $aggregationByClient['balance'] = $dataToday['withdrawals_balance'];
            }
        }
        return $aggregationByClient;
    }

    public function calculateForOnceDay($transactionList)
    {
        $clientContractDetail = ClientContractDetail::where('client_id')->first();
        $rate = $clientContractDetail ? $clientContractDetail->contract_rate : 0;

        foreach ($transactionList as $item) {
            $item->number_refunds = $this->getNumberOfHistory($item, ChargeHistory::REFUND);
            $item->refund_amount = $this->getTotalAmountFee($item, ChargeHistory::REFUND);
            $item->settlement_amount = $this->getTotalAmountFee($item, ChargeHistory::SETTLEMENT);
            $item->settlement_fee = $rate * $item->settlement_amount;
            $item->charge_amount = $this->getTotalAmountFee($item, ChargeHistory::CHARGE);
            $item->charge_fee = $rate * $item->charge_amount;
            $item->system_usage_fee = ($item->amount - $item->refund_amount) * $rate / 100;
            $item->account_fee = ($item->amount - $item->refund_amount) * $rate / 100;
        }
        return $transactionList;
    }

    public function getTotalAmountFee($transactionList, int $type)
    {
        $total = 0;
        if ($transactionList instanceof \Illuminate\Database\Eloquent\Model) {
            $history = $transactionList->history;
            $list = $history->where('type', $type);
            if (ChargeHistory::TRANSFER == $type) {
                return $list->sum('charge_fee') + $list->sum('transfer_amount');
            }
            return $list->sum('charge_fee') + $list->sum('payment_amount');
        } else {
            $history = $transactionList->pluck('history');
            $history = $history->where('type', $type);
            foreach ($history as $item) {
                if (ChargeHistory::TRANSFER == $type) {
                    $total +=  $history->sum('charge_fee') + $history->sum('transfer_amount');
                } else {
                    $total += $item->sum('charge_fee') + $item->sum('payment_amount');
                }
            }
        }
        return $total;
    }

    public function getNumberOfHistory($transactionList, int $type)
    {
        $count = 0;
        if ($transactionList instanceof \Illuminate\Database\Eloquent\Model) {
            $history = $transactionList->history;
            $history = $history->where('type', $type);
            return $history->count();
        } else {
            $history = $transactionList->pluck('history');
            $history = $history->where('type', $type);
            foreach ($history as $item) {
                $count += $item->count();
            }
        }

        return $count;
    }

    public function getClientAggregationByClientId($clientId, $fromDate = null)
    {
        return $this->clientAggregationRepository->getClientAggregationByClientId($clientId, $fromDate);
    }

    public function getSummaryClientAggregation(Request $request)
    {
        return $this->clientAggregationRepository->getSummaryClientAggregation($request);
    }

    public function exportCsv($depositList, $withdrawalsList, $request)
    {
        $fileName = '';
        $date = Carbon::createFromFormat('Y-m-d', $request->filter_date);

        $fileName = 'deposit_withdrawal_';

        if ($request->client_id) {
            $fileName .= $request->client_id . '_';
        }

        if ($request->filter_type == 'day') {
            $fileName .= $date->format('Ymd') . '.csv';
        } else if ($request->filter_type == 'month') {
            $fileName .= $date->format('Ym') . '.csv';
        } else if ($request->filter_type == 'year') {
            $fileName .= $date->format('Y') . '.csv';
        } else {
            $fileName .= '.csv';
        }
        $filePath = "csv/$fileName";

        $dataList = [];

        if (isset($request->language)) {
            if ($request->language == "jp")
                $language = "jp";
            else
                $language = "en";

            app()->setLocale($language);
        }

        $invoice = new InvoicesExport();

        // User Admin see column "Admin note"
        if (Auth::user()->client_id == null) {
            $invoice->addHeadingAdmin();
        }

        $templateHeader = $invoice->getHeaderTemplate();
        $keyTemplateHeader = array_keys($templateHeader);

        $depositAccountNumber = [];

        foreach ($depositList as $deposits) {
            foreach ($deposits as $data) {
                $key = $data->client_id . '_' . Carbon::parse($data->date)->format('Ymd') . '_' . $data->account_number;

                if (!isset($dataList[$key])) {
                    $dataList[$key] = $templateHeader;
                }

                $attributes = get_object_vars($data);
                $depositKeys = array_keys($attributes);

                foreach ($depositKeys as $dataKey)
                {
                    if (in_array($dataKey, $keyTemplateHeader)) {
                        if ($dataKey == 'system_usage_fee')
                        {
                            // round data
                            $value = number_format(Arr::get($attributes, $dataKey) + 0.49, 0, '.', '');
                        }
                        elseif ($dataKey == 'date') {
                            $value = Carbon::parse(Arr::get($attributes, $dataKey))->format('Y/m/d');
                        }
                        else{
                            $value = Arr::get($attributes, $dataKey);
                            if (is_numeric($value))
                                $value = strval($value);
                        }
                        $dataList[$key][$dataKey] = $value;
                    }
                }

                $memo = "";
                if (isset($data->charge_history) and count($data->charge_history) > 0)
                {
                    foreach ($data->charge_history as $chargeHistory) {
                        if ($memo != "")
                            $memo .= '; ';
                        $memo .= $chargeHistory->memo;
                    }
                }
                $dataList[$key]['memo'] = $memo;

                $depositAccountNumber[$data->client_id] = $data->account_number;
            }
        }

        foreach ($withdrawalsList as $withdrawals) {
            foreach ($withdrawals as $data) {
                $key = $data->client_id. '_' . Carbon::parse($data->date)->format('Ymd') . '_' . $data->account_number;

                if (!isset($dataList[$key])) {
                    $dataList[$key] = $templateHeader;
                }

                $attributes = get_object_vars($data);
                $withdrawalKeys = array_keys($attributes);

                foreach ($withdrawalKeys as $dataKey)
                {
                    if (in_array($dataKey . '_2', $keyTemplateHeader)) {
                        if ($dataKey == 'system_usage_fee')
                        {
                            // round data
                            $value = number_format(Arr::get($attributes, $dataKey) + 0.49, 0, '.', '');
                        }
                        else{
                            $value = Arr::get($attributes, $dataKey);
                            if (is_numeric($value))
                                $value = strval($value);
                        }
                        $dataList[$key][$dataKey . '_2'] = $value;
                    }

                    # Action when row`s deposit is null
                    if ($dataKey == 'date') {
                        if ($dataList[$key][$dataKey] == ""){
                            $value = Carbon::parse(Arr::get($attributes, $dataKey))->format('Y/m/d');
                            $dataList[$key][$dataKey] = $value;
                        }
                    } elseif ($dataKey == 'account_number') {
                        if ($dataList[$key][$dataKey] == "")
                            $dataList[$key][$dataKey] = $depositAccountNumber[$data->client_id];
                    }
                }

                $memo = "";
                $adminNote = "";

                if (isset($data->charge_history) and count($data->charge_history) > 0)
                {

                    foreach ($data->charge_history as $chargeHistory) {
                        if ($memo != "")
                            $memo .= '; ';

                        if ($adminNote != "")
                            $adminNote .= '; ';

                        $memo .= $chargeHistory->memo;
                        $adminNote .= $chargeHistory->memo_internal;
                    }
                }

                $dataList[$key]['memo_2'] = $memo;

                // User Admin see column "Admin note"
                if (Auth::user()->client_id == null) {
                    $dataList[$key]['admin_note'] = $adminNote;
                }
            }
        }

        // Sort List
        krsort($dataList);

        $invoice->importData(array_values($dataList));

        Excel::store($invoice, $filePath, 'public');

        $url = config('app.url') . '/download?filename=' . $fileName;

        return response()->json(['url' => $url]);
    }

    public function getChargeHistory($transactionList, $clientId, $request)
    {
        foreach ($transactionList as $item) {
            if ($request->is_sum && $request->group_by_year) {
                $fromDate = Carbon::parse($item->date)->startOfYear()->format('Y-m-d H:i:s');
                $toDate = Carbon::parse($item->date)->endOfYear()->format('Y-m-d H:i:s');
            }else if ($request->is_sum && $request->group_by_month){
                $fromDate = Carbon::parse($item->date)->startOfMonth()->format('Y-m-d H:i:s');
                $toDate = Carbon::parse($item->date)->endOfMonth()->format('Y-m-d H:i:s');
            } else {
                $fromDate = $item->date;
                $toDate = $item->date;
            }
            if ($request->csv) {
                $chargeHistory = $this->chargeHistoryRepository->getByAccountNumber($clientId, $fromDate, $toDate, $item->account_number);
            } else {
                $chargeHistory = $this->chargeHistoryRepository->getByTypeAggregation($clientId, $fromDate, $toDate, $item->type);
            }

            $item->settlement_amount = 0;
            $item->settlement_fee = 0;
            $item->dewi_amount = 0;
            $item->dewi_fee = 0;
            $item->number_dewi = 0;
            $item->number_refunds = 0;
            $item->refund_amount = 0;
            $item->refund_fee = 0;
            $item->charge_amount = 0;
            $item->charge_fee = 0;
            $item->transfer_amount = 0;
            $item->transfer_fee = 0;
            if ($chargeHistory->isNotEmpty()) {
                $chargeHistory = $chargeHistory->sortByDesc('id')->values()->all();
                $actualBalance = $item->actual_balance;
                foreach ($chargeHistory as $row) {
                    $row->actual_balance = $actualBalance;
                    switch ($row->type) {
                        case ChargeHistory::INTEREST:
                        case ChargeHistory::REPAYMENT:
                            $actualBalance = $actualBalance - $row->payment_amount;
                            break;
                        case ChargeHistory::SETTLEMENT:
                            $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                            $item->settlement_amount += $row->payment_amount;
                            $item->settlement_fee += $row->charge_fee;
                            break;
                        case ChargeHistory::REFUND:
                            if ($item->type == 1) {
                                $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                            } else {
                                $actualBalance = $actualBalance - $row->payment_amount + $row->charge_fee;
                            }
                            $item->refund_amount += $row->payment_amount;
                            $item->number_refunds += 1;
                            $item->refund_fee += $row->charge_fee;
                            break;
                        case ChargeHistory::CHARGE:
                            if ($item->type == 2) {
                                $actualBalance = $actualBalance - $row->payment_amount + $row->charge_fee;
                                $item->charge_amount += $row->payment_amount;
                                $item->charge_fee += $row->charge_fee;
                            }
                            break;
                        case ChargeHistory::DEPOSIT_WITHDRAWAL:
                            if ($item->type == 1) {
                                $actualBalance = $actualBalance - $row->payment_amount;
                            } else {
                                $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                            }
                            $item->dewi_amount += $row->payment_amount;
                            $item->dewi_fee += $row->charge_fee;
                            $item->number_dewi += 1;
                            break;
                        case ChargeHistory::TRANSFER:
                            if ($item->type == 1) {
                                $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                            } else {
                                $actualBalance = $actualBalance - $row->payment_amount;
                            }
                            $item->transfer_amount += $row->payment_amount;
                            $item->transfer_fee += $row->charge_fee;
                            break;
                        case ChargeHistory::MISC:
                            $actualBalance = $actualBalance - $row->payment_amount + $row->charge_fee;
                            break;
                        case ChargeHistory::BORROWING:
                            $actualBalance = $actualBalance + $row->payment_amount;
                            break;
                    }
                }
            }
            $item->number_trans = $item->number_trans - $item->number_refunds + $item->number_dewi;
            $item->amount = $item->amount - $item->refund_amount + $item->dewi_amount;
            $item->system_usage_fee = ceil($item->amount * $item->system_usage_rate / 100);

            $item->charge_history = $chargeHistory;
        }
    }

    public function getAccountBalance($transactionList, $clientId, $request)
    {
        $accounts = Account::where('client_id', '=', $clientId)
            ->where('service_type', '=', $transactionList->first()->type)->get();
        foreach ($transactionList as $item) {
            if ($request->is_sum) {
                $date = $request->group_by_date
                    ? Carbon::parse($request->to_date)->endOfDay()->format('Y-m-d H:i:s')
                    : Carbon::parse($item->date)->endOfMonth()->format('Y-m-d H:i:s');
                if ($request->group_by_year) {
                    $date = Carbon::parse($request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
                }
            } else {
                $date = $item->date;
            }

            foreach ($accounts as $account) {
                $accountBalance = $this->accountBalanceHistoryRepository->getAccountBalanceByDate($clientId, $account->account_number, $date, $item->type);
                if ($accountBalance) {
                    $item->account_balance += $accountBalance->balance;
                }
            }
            $borrowing = 0;
            $borrowingList = $this->chargeHistoryRepository->getBorrowingByClient($clientId, $date, $item->type);
            foreach ($borrowingList as $borrow) {
                $borrowing -= $borrow->payment_amount;
            }

            $repayment = 0;
            $repaymentList = $this->chargeHistoryRepository->getRepaymentByClient($clientId, $date, $item->type);
            foreach ($repaymentList as $repay) {
                $repayment += $repay->payment_amount;
            }

            $interest = 0;
            $interestList = $this->chargeHistoryRepository->getInterestByClient($clientId, $date, $item->type);
            foreach ($interestList as $inter) {
                $interest += $inter->payment_amount;
            }

            $misc = 0;
            $miscList = $this->chargeHistoryRepository->getMiscByClient($clientId, $date, $item->type);
            foreach ($miscList as $mc) {
                $misc += $mc->payment_amount - $mc->charge_fee;
            }

            $transferFee = 0;
            if ($item->type == 1) {
                $transferList = $this->chargeHistoryRepository->getTransferByClient($clientId, $date, $item->type);
                foreach ($transferList as $transfer) {
                    $transferFee -= $transfer->charge_fee;
                }
            }

            $item->actual_balance = $item->account_balance + $borrowing + $repayment + $interest + $misc + $transferFee;
        }
    }

    public function getAccountBalanceIncome($item, $clientId, $request, $isMonth = false)
    {
        $accounts = Account::where('client_id', '=', $clientId)
            ->where('service_type', '=', $item->type)->get();
        $date = Carbon::parse($request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
        if ($isMonth) {
            $date = Carbon::parse($item->date)->endOfMonth()->format('Y-m-d H:i:s');
        }
        foreach ($accounts as $account) {
            $accountBalance = $this->accountBalanceHistoryRepository->getAccountBalanceByDate($clientId, $account->account_number, $date, $item->type);
            if ($accountBalance) {
                $item->account_balance += $accountBalance->balance;
            }
        }
        $borrowing = 0;
        $borrowingList = $this->chargeHistoryRepository->getBorrowingByClient($clientId, $date, $item->type);
        foreach ($borrowingList as $borrow) {
            $borrowing -= $borrow->payment_amount;
        }

        $repayment = 0;
        $repaymentList = $this->chargeHistoryRepository->getRepaymentByClient($clientId, $date, $item->type);
        foreach ($repaymentList as $repay) {
            $repayment += $repay->payment_amount;
        }

        $interest = 0;
        $interestList = $this->chargeHistoryRepository->getInterestByClient($clientId, $date, $item->type);
        foreach ($interestList as $inter) {
            $interest += $inter->payment_amount;
        }

        $misc = 0;
        $miscList = $this->chargeHistoryRepository->getMiscByClient($clientId, $date, $item->type);
        foreach ($miscList as $mc) {
            $misc += $mc->payment_amount - $mc->charge_fee;
        }

        $transferFee = 0;
        if ($item->type == 1) {
            $transferList = $this->chargeHistoryRepository->getTransferByClient($clientId, $date, $item->type);
            foreach ($transferList as $transfer) {
                $transferFee -= $transfer->charge_fee;
            }
        }

        $item->actual_balance = $item->account_balance + $borrowing + $repayment + $interest + $misc + $transferFee;
        return $item;
    }

    public function getAllChargeIncome(Request $request, $item, $isMonth = false)
    {
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        if ($request->group_by_month) {
            $fromDate = Carbon::parse($request->from_date)->startOfMonth()->format('Y-m-d');
            $toDate = Carbon::parse($request->to_date)->endOfMonth()->format('Y-m-d');
        }
        if ($request->group_by_year) {
            $fromDate = Carbon::parse($request->from_date)->startOfYear()->format('Y-m-d');
            $toDate = Carbon::parse($request->to_date)->endOfYear()->format('Y-m-d');
        }


        if ($isMonth) {
            $fromDate = Carbon::parse($item->date)->startOfMonth()->format('Y-m-d');
            $toDate = Carbon::parse($item->date)->endOfMonth()->format('Y-m-d');
            $invoices = Invoice::where('period_from', $fromDate)
                ->where('period_to', $toDate)
                ->orderByDesc('invoice_no')->get();
        } else {
            $invoices = Invoice::where('client_id', $item->client_id)
                ->where('period_from', '>=', $fromDate)
                ->where('period_to', '<=', $toDate)
                ->orderByDesc('invoice_no')->get();
        }

        $item->adjustment = 0;
        foreach ($invoices as $invoice) {
            $item->adjustment += $invoice->discount_amount + ceil($invoice->total_tax);
        }

        $chargeHistory = $this->chargeHistoryRepository->getByTypeAggregation($item->client_id, $fromDate, $toDate, $item->type);
        $item->settlement_amount = 0;
        $item->settlement_fee = 0;
        $item->dewi_amount = 0;
        $item->dewi_fee = 0;
        $item->number_dewi = 0;
        $item->number_refunds = 0;
        $item->refund_amount = 0;
        $item->refund_fee = 0;
        $item->charge_amount = 0;
        $item->charge_fee = 0;
        $item->transfer_amount = 0;
        $item->transfer_fee = 0;
        $item->borrowing = 0;
        $item->interest_fee = 0;
        $actualBalance = $item->actual_balance;
        foreach ($chargeHistory as $row) {
            $row->actual_balance = $actualBalance;
            switch ($row->type) {
                case ChargeHistory::INTEREST:
                    $actualBalance = $actualBalance - $row->payment_amount;
                    $item->interest_fee += $row->payment_amount;
                    break;
                case ChargeHistory::REPAYMENT:
                    $actualBalance = $actualBalance - $row->payment_amount;
                    break;
                case ChargeHistory::SETTLEMENT:
                    $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                    $item->settlement_amount += $row->payment_amount;
                    $item->settlement_fee += $row->charge_fee;
                    break;
                case ChargeHistory::REFUND:
                    if ($item->type == 1) {
                        $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                    } else {
                        $actualBalance = $actualBalance - $row->payment_amount + $row->charge_fee;
                    }
                    $item->refund_amount += $row->payment_amount;
                    $item->number_refunds += 1;
                    $item->refund_fee += $row->charge_fee;
                    break;
                case ChargeHistory::CHARGE:
                    if ($item->type == 2) {
                        $actualBalance = $actualBalance - $row->payment_amount + $row->charge_fee;
                    }
                    $item->charge_amount += $row->payment_amount;
                    $item->charge_fee += $row->charge_fee;
                    break;
                case ChargeHistory::DEPOSIT_WITHDRAWAL:
                    if ($item->type == 1) {
                        $actualBalance = $actualBalance - $row->payment_amount;
                    } else {
                        $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                    }
                    $item->dewi_amount += $row->payment_amount;
                    $item->dewi_fee += $row->charge_fee;
                    $item->number_dewi += 1;
                    break;
                case ChargeHistory::TRANSFER:
                    if ($item->type == 1) {
                        $actualBalance = $actualBalance + $row->payment_amount + $row->charge_fee;
                    } else {
                        $actualBalance = $actualBalance - $row->payment_amount;
                    }
                    $item->transfer_amount += $row->payment_amount;
                    $item->transfer_fee += $row->charge_fee;
                    break;
                case ChargeHistory::MISC:
                    $actualBalance = $actualBalance - $row->payment_amount + $row->charge_fee;
                    break;
                case ChargeHistory::BORROWING:
                    $actualBalance = $actualBalance + $row->payment_amount;
                    $item->borrowing += $row->payment_amount;
                    break;
            }

        }
        $item->number_trans = $item->number_trans - $item->number_refunds + $item->number_dewi;
        $item->amount = $item->amount - $item->refund_amount + $item->dewi_amount;
        $item->system_usage_fee = ceil($item->amount * $item->system_usage_rate / 100);
        return $item;

    }

    public function getInComeAndExpenditure(Request $request)
    {
        $transactionsList = $this->clientAggregationRepository->getInComeAndExpenditure($request);
        $this->getReferralFee($transactionsList);

        $depositList = $transactionsList->where('type', 1);
        $withdrawalsList = $transactionsList->where('type', 2);


        $toDate = Carbon::parse($request->to_date)->endOfDay();
        if ($request->group_by_year) {
            $toDate = Carbon::parse($request->to_date)->endOfYear();
            if (Carbon::now()->isSameYear($toDate)) {
                $toDate = Carbon::now()->endOfMonth();
            }
        }
        if ($request->group_by_month) {
            $toDate = Carbon::parse($request->to_date)->endOfMonth();
        }
        $depositToday = collect();
        $withdrawalToday = collect();
        if (Carbon::now()->isSameMonth($toDate)) {
            $depositToday = $this->getRequestAllClientToday();
            $withdrawalToday = $this->getWithdrawalAllClientToday();
        }

        $depositByClientData = [];
        $withdrawalsByClientDate = [];

        $listSumDepositByClient = $depositList->groupBy('client_id');
        $listSumWithDrawalByClient = $withdrawalsList->groupBy('client_id');

        if (Carbon::parse($request->from_date)->isSameDay(Carbon::now())
            && Carbon::parse($request->to_date)->isSameDay(Carbon::now())
            && $request->group_by_date
        ) {
            $ids = collect();
            if ($depositToday->isNotEmpty()) {
                foreach ($depositToday->pluck('user_id') as $id) {
                    $ids->push($id);
                }
            }
            if ($withdrawalToday->isNotEmpty()) {
                foreach ($withdrawalToday->pluck('user_id') as $id) {
                    $ids->push($id);
                }
            }
            $clients = Client::withTrashed()->whereIn('client_id', $ids->unique())->get();
            foreach ($clients as $client) {
                $listSumDepositByClient[] = $this->prepareDataToday($client, 1);
                $listSumWithDrawalByClient[] = $this->prepareDataToday($client, 2);
            }
            $listSumDepositByClient = collect($listSumDepositByClient)->groupBy('client_id');
            $listSumWithDrawalByClient = collect($listSumWithDrawalByClient)->groupBy('client_id');
        }

        foreach ($listSumDepositByClient as $key => $item) {
            $data = $this->sumInComeByClient($item);
            $data = $this->getAccountBalanceIncome($data, $key, $request);
            $data = $this->getAllChargeIncome($request, $data);
            $dataToday = $depositToday->where('user_id', $key);
            $data->amount += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
            $data->system_usage_fee = ceil($data->amount * $data->system_usage_rate / 100);
            $data->account_balance += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
            $data->actual_balance += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
            $data->number_trans += $dataToday->isNotEmpty() ? $dataToday->sum('number_of_deposit') : 0;
            $depositByClientData[] = $data;
        }

        foreach ($listSumWithDrawalByClient as $key => $item) {
            $data = $this->sumInComeByClient($item);
            $data = $this->getAccountBalanceIncome($data, $key, $request);
            $data = $this->getAllChargeIncome($request, $data);
            $dataToday = $withdrawalToday->where('user_id', $key);
            $data->amount += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
            $data->system_usage_fee = ceil($data->amount * $data->system_usage_rate / 100);
            $data->account_balance -= $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
            $data->actual_balance -= $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
            $data->number_trans += $dataToday->isNotEmpty() ? $dataToday->sum('number_of_withdrawal') : 0;
            $withdrawalsByClientDate[] = $data;
        }

        $depositByMonth = [];
        $withdrawalByMonth = [];

        if ($request->is_sum) {
            $listSumDeposit = $depositList->groupBy('month');
            $listSumWithDrawal = $withdrawalsList->groupBy('month');

            foreach ($listSumDeposit as $key => $item) {
                $listSubDeposit = $item->groupBy('client_id');
                $depositByClientMonth = [];
                foreach ($listSubDeposit as $clientId => $row) {
                    $data = $this->sumInComeByClient($row);
                    $data = $this->getAccountBalanceIncome($data, $clientId, $request, true);
                    $data = $this->getAllChargeIncome($request, $data, true);
                    if ($key == Carbon::now()->format('m-Y')) {
                        $dataToday = $depositToday->where('user_id', $clientId);
                        $data->amount += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                        $data->system_usage_fee = ceil($data->amount * $data->system_usage_rate / 100);
                        $data->account_balance += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                        $data->actual_balance += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                        $data->number_trans += $dataToday->isNotEmpty() ? $dataToday->sum('number_of_deposit') : 0;
                    }
                    $depositByClientMonth[] = $data;
                }

                $depositByMonth[$key] = $this->sumInComeAllClient(collect($depositByClientMonth));
            }

            foreach ($listSumWithDrawal as $key => $item) {
                $listSubWithDrawal = $item->groupBy('client_id');
                $withdrawalByClientMonth = [];
                foreach ($listSubWithDrawal as $client => $row) {
                    $data = $this->sumInComeByClient($row);
                    $data = $this->getAccountBalanceIncome($data, $client, $request, true);
                    $data = $this->getAllChargeIncome($request, $data, true);
                    if ($key == Carbon::now()->format('m-Y')) {
                        $dataToday = $withdrawalToday->where('user_id', $client);
                        $data->amount += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                        $data->system_usage_fee = ceil($data->amount * $data->system_usage_rate / 100);
                        $data->account_balance -= $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                        $data->actual_balance -= $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                        $data->number_trans += $dataToday->isNotEmpty() ? $dataToday->sum('number_of_withdrawal') : 0;
                    }
                    $withdrawalByClientMonth[] = $data;
                }

                $withdrawalByMonth[$key] = $this->sumInComeAllClient(collect($withdrawalByClientMonth));
                $withdrawalByMonth[$key]->other_expense = 0;
                $fromDate = Carbon::parse($item->first()->date)->startOfMonth()->format('Y-m-d');
                $toDate = Carbon::parse($item->first()->date)->endOfMonth()->format('Y-m-d');
                $invoice = IncomeExpenditure::where('from_date', $fromDate)
                    ->where('to_date', $toDate)->first();

                if ($invoice) {
                    $invoice = $invoice->load('incomeExpenditureDetails');
                    $otherExpense = $invoice->incomeExpenditureDetails->isNotEmpty()
                        ? $invoice->incomeExpenditureDetails->where('type', 4)->where('type_fee', 6)
                        : collect([]);
                    if ($otherExpense->isNotEmpty()) {
                        $withdrawalByMonth[$key]->other_expense = $otherExpense->sum('profit');
                    }

                }
            }
        }

        return [
            'deposit' => $depositByClientData,
            'withdrawal' => $withdrawalsByClientDate,
            'deposit_month' => $depositByMonth,
            'withdrawal_month' => $withdrawalByMonth
        ];
    }

    public function prepareDataToday($client, $type)
    {
        $month['type'] = $type;
        $month['month'] = Carbon::now()->format('m-Y');
        $month['date'] = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $month['client_id'] = $client->client_id;
        $month['represent_name'] = $client->represent_name;
        $month['amount'] = 0;
        $month['number_trans'] = 0;
        $month['account_fee'] = 0;
        $month['system_usage_fee'] = 0;
        $month['system_usage_rate'] = $client->system_usage_rate;
        $month['commission_bank_fee'] = 0;
        $month['transfer_fee_different'] = 0;
        $month['settlement_fee'] = 0;
        $month['refund_fee'] = 0;
        $month['charge_fee'] = 0;
        $month['borrowing'] = 0;
        $month['account_balance'] = 0;
        $month['referal_fee'] = 0;
        $month['total_expenses'] = 0;
        return (object)$month;
    }

    public function sumInComeByClient(Collection $list)
    {
        $month = [];
        if ($list->isNotEmpty()) {
            $month['type'] = $list->first()->type;
            $month['month'] = $list->first()->month;
            $month['date'] = $list->first()->date;
            $month['client_id'] = $list->first()->client_id;
            $month['represent_name'] = $list->first()->represent_name;
            $month['amount'] = $list->sum('amount');
            $month['number_trans'] = $list->sum('number_trans');
            $month['account_fee'] = floor($list->sum('account_fee'));
            $month['system_usage_fee'] = $list->sum('system_usage_fee');
            $month['system_usage_rate'] = $list->first()->system_usage_rate;
            $month['commission_bank_fee'] = $list->sum('commission_bank_fee');
            $month['transfer_fee_different'] = $list->sum('transfer_fee_different');
            $month['settlement_fee'] = $list->sum('settlement_fee');
            $month['refund_fee'] = $list->sum('refund_fee');
            $month['charge_fee'] = $list->sum('charge_fee');
            $month['borrowing'] = $list->sum('borrowing');
            $month['account_balance'] = $list->first()->account_balance;
            $month['referal_fee'] = $list->sum('referal_fee');
            $month['total_expenses'] = $list->sum('total_expenses');
        }
        return (object)$month;
    }

    public function sumInComeAllClient(Collection $list)
    {
        $month = [];
        if ($list->isNotEmpty()) {
            $month['type'] = $list->first()->type;
            $month['date'] = $list->first()->date;
            $month['month'] = $list->first()->month;
            $month['amount'] = $list->sum('amount');
            $month['number_trans'] = $list->sum('number_trans');
            $month['account_fee'] = $list->sum('account_fee');
            $month['system_usage_fee'] = $list->sum('system_usage_fee');
            $month['commission_bank_fee'] = $list->sum('commission_bank_fee');
            $month['transfer_fee_different'] = $list->sum('transfer_fee_different');
            $month['settlement_fee'] = $list->sum('settlement_fee');
            $month['refund_fee'] = $list->sum('refund_fee');
            $month['charge_fee'] = $list->sum('charge_fee');
            $month['borrowing'] = $list->sum('borrowing');
            $month['account_balance'] = $list->sum('account_balance');
            $month['referal_fee'] = $list->sum('referal_fee');
            $month['interest_fee'] = $list->sum('interest_fee');
            $month['transfer_fee'] = $list->sum('transfer_fee');
            $month['total_expenses'] = $list->sum('total_expenses');
            $month['actual_balance'] = $list->sum('actual_balance');
        }

        return (object)$month;
    }

    public function getReferralFee(Collection $list)
    {
        $listAllIntroducer = IntroducerInformation::all();

        foreach ($list as $row) {
            // get introducer rate by client
            $introducerFee = 0;
            $introducer = $listAllIntroducer->where('client_id', $row->client_id ?? -1)->first();
            if ($introducer) {
                $introducerFee = $row->amount * $introducer->referral_fee / 100;
            }
            // get account introducer by client
            $accountIntroductionFee = 0;
            $introducer = $listAllIntroducer->where('account_contractor_id', $row->contractor_id ?? -1)->first();
            if ($introducer) {
                $accountIntroductionFee = $row->amount * $introducer->referral_fee / 100;
            }
            $row->account_fee = floor($row->account_fee);
            $row->referal_fee = floor($introducerFee) + floor($accountIntroductionFee);
            $row->total_expenses = $row->account_fee + $row->commission_bank_fee + $row->referal_fee;
        }
    }

    public function getRequestAllClientToday()
    {
        return $this->clientAggregationRepository->getRequestAllClientToday();
    }

    public function getWithdrawalAllClientToday($isAccount = false)
    {
        return $this->clientAggregationRepository->getWithdrawalAllClientToday($isAccount);
    }

    public function getSummaryIncomeExpenditure(Request $request)
    {
        $depositToday = collect();
        $withdrawalToday = collect();
        if (Carbon::now()->isSameMonth(Carbon::parse($request->from_date))) {
            $depositToday = $this->getRequestAllClientToday();
            $withdrawalToday = $this->getWithdrawalAllClientToday();
        }

        $fromDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d');
        $toDate = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->endOfMonth()->format('Y-m-d');

        $fromDatePrevious = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->subMonth()->startOfMonth()->format('Y-m-d');
        $toDatePrevious = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->subMonth()->endOfMonth()->format('Y-m-d');

        $incomeExpenditureData = IncomeExpenditure::firstOrNew([
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);
        $incomeExpenditureData->save();
        $dataClientAggregation = $this->clientAggregationRepository->getSummaryIncomeExpenditure($request);
        $dataDeposit = $dataClientAggregation->where('type', 1)->groupBy('client_id');
        $dataWithdrawal = $dataClientAggregation->where('type', 2)->groupBy('client_id');
        $dataChargeCompare = $this->chargeHistoryRepository->getListIncomeRefundDewi($request);
        $dataCharge = $this->chargeHistoryRepository->getListIncome($request)->groupBy('client_id');
        $deleteTotal = IncomeExpenditureDetail::where('income_expenditure_id', $incomeExpenditureData->id)
            ->where('item_name', 'TOTAL')->where('type', '>=', 1)->where('type', '<=', 2)->delete();
        $incomeDetailData = IncomeExpenditureDetail::where('income_expenditure_id', $incomeExpenditureData->id)->get();

        //get income previous
        $incomePreviousData = IncomeExpenditure::firstOrNew([
            'from_date' => $fromDatePrevious,
            'to_date' => $toDatePrevious
        ]);
        $incomePreviousDetailData = IncomeExpenditureDetail::where('income_expenditure_id', $incomePreviousData->id)->get();

        // generate type deposit
        foreach ($dataDeposit as $clientId => $ca) {
            // delete row total
            $incomeDetail = $incomeDetailData->where('type', 1)
                ->where('client_id', $clientId)
                ->where('item_name', 'TOTAL')
                ->first();
            if ($incomeDetail) {
                $incomeDetail->delete();
            }


            $chargeRefund = $dataChargeCompare->where('client_id', $clientId)
                ->where('type_client_aggregation', $ca->first()->type)
                ->where('type', ChargeHistory::REFUND);
            $chargeDewi = $dataChargeCompare->where('client_id', $clientId)
                ->where('type_client_aggregation', $ca->first()->type)
                ->where('type', ChargeHistory::DEPOSIT_WITHDRAWAL);
            $dataToday = $depositToday->where('user_id', $clientId);

            $amountChagreRefund = $chargeRefund->isNotEmpty() ? $chargeRefund->sum('payment_amount') : 0;
            $numberChagreRefund = $chargeRefund->isNotEmpty() ? $chargeRefund->count() : 0;
            $amountChagreDewi = $chargeDewi->isNotEmpty() ? $chargeDewi->sum('payment_amount') : 0;
            $numberChagreDewi = $chargeDewi->isNotEmpty() ? $chargeDewi->count() : 0;

            $amountChagreDewi = $amountChagreDewi + ($dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0);
            $numberChagreDewi = $numberChagreDewi + ($dataToday->isNotEmpty() ? $dataToday->sum('number_of_deposit') : 0);

            $client = Client::withTrashed()->where('client_id', $clientId)->first();
            $client = $client ? $client->load('client_details') : null;
            if ($client) {
                if ($client->contract_method > 0) {
                    $clienContracts = $client->client_details ? $client->client_details->where('service_type', 1) : collect([]);

                    $contractBase = $clienContracts->where('max_amount', 0)->first();
                    $contractRange = $clienContracts->where('max_amount', '>', 0)->sortBy([
                        ['max_amount', 'asc']
                    ]);

                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => $ca->first()->type,
                        'client_id' => $ca->first()->client_id,
                        'rate' => 0,
                        'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                        'amount' => $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi,
                        'profit' => 0,
                        'previous_month' => 0,
                        'item_name' => 'TOTAL',
                    ]);

                    $amount = $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi;
                    $amountLine = 0;

                    foreach ($contractRange as $contract) {
                        if ($amount > $contract->max_amount && $amountLine < $contract->max_amount) {
                            $amountLine = $contract->max_amount - $amountLine;
                            $amount = $amount - $amountLine;
                        } else {
                            $amountLine = max($amount, 0);
                            $amount = max($amount, 0) - $amountLine;
                        }
                        $incomeDetail = $incomeDetailData->where('type', 1)
                            ->where('client_id', $clientId)
                            ->where('rate', $contract->contract_rate)
                            ->first();

                        $incomePreviousDetail = $incomePreviousDetailData->where('type', 1)
                            ->where('client_id', $clientId)
                            ->where('rate', $contract->contract_rate)
                            ->first();

                        if ($incomeDetail) {
                            $incomeDetail->item_name = $contract->description;
                            $incomeDetail->number_transaction = $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi;
                            $incomeDetail->amount = $amountLine;
                            $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                            $incomeDetail->profit = ceil($amountLine * $contract->contract_rate / 100);
                            $incomeDetail->save();
                        } else {
                            IncomeExpenditureDetail::create([
                                'income_expenditure_id' => $incomeExpenditureData->id,
                                'type' => $ca->first()->type,
                                'client_id' => $ca->first()->client_id,
                                'rate' => $contract->contract_rate,
                                'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                                'amount' => $amountLine,
                                'profit' => ceil($amountLine * $contract->contract_rate / 100),
                                'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                                'item_name' => $contract->description,
                            ]);
                        }
                    }

                    if ($contractBase) {
                        $incomeDetail = $incomeDetailData->where('type', 1)
                            ->where('client_id', $clientId)
                            ->where('rate', $contractBase->contract_rate)
                            ->first();

                        $incomePreviousDetail = $incomePreviousDetailData->where('type', 1)
                            ->where('client_id', $clientId)
                            ->where('rate', $contractBase->contract_rate)
                            ->first();

                        $amountLine = max($amount, 0);

                        if ($incomeDetail) {
                            $incomeDetail->item_name = $contractBase->description;
                            $incomeDetail->number_transaction = $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi;
                            $incomeDetail->amount = $amountLine;
                            $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                            $incomeDetail->profit = ceil($amountLine * $incomeDetail->rate / 100);
                            $incomeDetail->save();
                        } else {
                            IncomeExpenditureDetail::create([
                                'income_expenditure_id' => $incomeExpenditureData->id,
                                'type' => $ca->first()->type,
                                'client_id' => $ca->first()->client_id,
                                'rate' => $contractBase->contract_rate,
                                'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                                'amount' => $amountLine,
                                'profit' => ceil($amountLine * $contractBase->contract_rate / 100),
                                'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                                'item_name' => $contractBase->description,
                            ]);
                        }
                    }

                } else {
                    $clienContracts = $client->client_details ? $client->client_details->where('service_type', 1) : collect([]);


                    $contractBase = $clienContracts->where('max_amount', 0)->first();
                    $incomeDetail = $incomeDetailData->where('type', 1)
                        ->where('client_id', $clientId)->first();
                    $incomePreviousDetail = $incomePreviousDetailData->where('type', 1)
                        ->where('client_id', $clientId)
                        ->first();
                    if ($contractBase) {
                        if ($incomeDetail) {
                            $incomeDetail->item_name = $contractBase->description;
                            $incomeDetail->number_transaction = $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi;
                            $incomeDetail->amount = $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi;
                            $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                            $incomeDetail->profit = ceil($ca->sum('amount') * $incomeDetail->rate / 100);
                            $incomeDetail->save();
                        } else {
                            IncomeExpenditureDetail::create([
                                'income_expenditure_id' => $incomeExpenditureData->id,
                                'type' => $ca->first()->type,
                                'client_id' => $ca->first()->client_id,
                                'rate' => $contractBase->contract_rate,
                                'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                                'amount' => $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi,
                                'profit' => ceil($ca->sum('amount') * $contractBase->contract_rate / 100),
                                'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                                'item_name' => $contractBase->description,
                            ]);
                        }
                    }
                }
            }
        }

        // generate type withdrawal
        foreach ($dataWithdrawal as $clientId => $ca) {

            $chargeRefund = $dataChargeCompare->where('client_id', $clientId)
                ->where('type_client_aggregation', $ca->first()->type)
                ->where('type', ChargeHistory::REFUND);
            $chargeDewi = $dataChargeCompare->where('client_id', $clientId)
                ->where('type_client_aggregation', $ca->first()->type)
                ->where('type', ChargeHistory::DEPOSIT_WITHDRAWAL);

            $dataToday = $withdrawalToday->where('user_id', $clientId);

            $amountChagreRefund = $chargeRefund->isNotEmpty() ? $chargeRefund->sum('payment_amount') : 0;
            $numberChagreRefund = $chargeRefund->isNotEmpty() ? $chargeRefund->count() : 0;
            $amountChagreDewi = $chargeDewi->isNotEmpty() ? $chargeDewi->sum('payment_amount') : 0;
            $numberChagreDewi = $chargeDewi->isNotEmpty() ? $chargeDewi->count() : 0;

            $amountChagreDewi = $amountChagreDewi + ($dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0);
            $numberChagreDewi = $numberChagreDewi + ($dataToday->isNotEmpty() ? $dataToday->sum('number_of_withdrawal') : 0);

            $client = Client::withTrashed()->where('client_id', $clientId)->first();
            $client = $client ? $client->load('client_details') : null;
            if ($client) {
                if ($client->contract_method) {
                    $clienContracts = $client->client_details ? $client->client_details->where('service_type', 2) : collect([]);
                    // line total
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => $ca->first()->type,
                        'client_id' => $ca->first()->client_id,
                        'rate' => 0,
                        'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                        'amount' => $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi,
                        'profit' => 0,
                        'previous_month' => 0,
                        'item_name' => 'TOTAL',
                    ]);

                    $contractBase = $clienContracts->where('max_amount', 0)->first();
                    $contractRange = $clienContracts->where('max_amount', '>', 0)->sortBy([
                        ['max_amount', 'asc']
                    ]);
                    $amount = $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi;
                    $amountLine = 0;

                    foreach ($contractRange as $contract) {
                        if ($amount > $contract->max_amount && $amountLine < $contract->max_amount) {
                            $amountLine = $contract->max_amount - $amountLine;
                            $amount = $amount - $amountLine;
                        } else {
                            $amountLine = max($amount, 0);
                            $amount = max($amount, 0) - $amountLine;
                        }
                        $incomeDetail = $incomeDetailData->where('type', 2)
                            ->where('client_id', $clientId)
                            ->where('rate', $contract->contract_rate)
                            ->first();
                        $incomePreviousDetail = $incomePreviousDetailData->where('type', 2)
                            ->where('client_id', $clientId)
                            ->where('rate', $contract->contract_rate)
                            ->first();

                        if ($incomeDetail) {
                            $incomeDetail->item_name = $contract->description;
                            $incomeDetail->number_transaction = $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi;
                            $incomeDetail->amount = $amountLine;
                            $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                            $incomeDetail->profit = ceil($amountLine * $incomeDetail->rate / 100);
                            $incomeDetail->save();
                        } else {
                            IncomeExpenditureDetail::create([
                                'income_expenditure_id' => $incomeExpenditureData->id,
                                'type' => $ca->first()->type,
                                'client_id' => $ca->first()->client_id,
                                'rate' => $contract->contract_rate,
                                'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                                'amount' => $amountLine,
                                'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                                'profit' => ceil($amountLine * $contract->contract_rate / 100),
                                'item_name' => $contract->description,
                            ]);
                        }
                    }

                    if ($contractBase) {
                        $incomeDetail = $incomeDetailData->where('type', 2)
                            ->where('client_id', $clientId)
                            ->where('rate', $contractBase->contract_rate)
                            ->first();
                        $incomePreviousDetail = $incomePreviousDetailData->where('type', 2)
                            ->where('client_id', $clientId)
                            ->where('rate', $contractBase->contract_rate)
                            ->first();

                        $amountLine = max($amount, 0);

                        if ($incomeDetail) {
                            $incomeDetail->item_name = $contractBase->description;
                            $incomeDetail->number_transaction = $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi;
                            $incomeDetail->amount = $amountLine;
                            $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                            $incomeDetail->profit = ceil($amountLine * $incomeDetail->rate / 100);
                            $incomeDetail->save();
                        } else {
                            IncomeExpenditureDetail::create([
                                'income_expenditure_id' => $incomeExpenditureData->id,
                                'type' => $ca->first()->type,
                                'client_id' => $ca->first()->client_id,
                                'rate' => $contractBase->contract_rate,
                                'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                                'amount' => $amountLine,
                                'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                                'profit' => ceil($amountLine * $contractBase->contract_rate / 100),
                                'item_name' => $contractBase->description,
                            ]);
                        }
                    }

                } else {
                    $clienContracts = $client->client_details ? $client->client_details->where('service_type', 2) : collect([]);

                    $contractBase = $clienContracts->where('max_amount', 0)->first();

                    if ($contractBase) {
                        $incomeDetail = $incomeDetailData->where('type', 2)
                            ->where('client_id', $clientId)
                            ->first();
                        $incomePreviousDetail = $incomePreviousDetailData->where('type', 2)
                            ->where('client_id', $clientId)
                            ->where('rate', $contractBase->contract_rate)
                            ->first();
                        if ($incomeDetail) {
                            $incomeDetail->item_name = $contractBase->description;
                            $incomeDetail->number_transaction = $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi;
                            $incomeDetail->amount = $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi;
                            $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                            $incomeDetail->profit = ceil($ca->sum('amount') * $incomeDetail->rate / 100);
                            $incomeDetail->save();
                        } else {
                            IncomeExpenditureDetail::create([
                                'income_expenditure_id' => $incomeExpenditureData->id,
                                'type' => $ca->first()->type,
                                'client_id' => $ca->first()->client_id,
                                'rate' => $contractBase->contract_rate,
                                'number_transaction' => $ca->sum('number_trans_exclude_refund') - $numberChagreRefund + $numberChagreDewi,
                                'amount' => $ca->sum('amount_exclude_refund') - $amountChagreRefund + $amountChagreDewi,
                                'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                                'profit' => ceil($ca->sum('amount') * $contractBase->contract_rate / 100),
                                'item_name' => $contractBase->description,
                            ]);
                        }
                    }
                }
            }
        }

        // generate type Miscellaneous Income
        foreach ($dataCharge as $clientId => $ca) {
            $data = $ca->groupBy('type');
            foreach ($data as $type => $row) {
                $itemName = '';
                $rate = 0;
                $interestAmount = 0;
                if($type == ChargeHistory::SETTLEMENT) {
                    $itemName = 'SETTLEMENT';
                    $rate = $row->first()->settlement_fee_rate;
                }
                if($type == ChargeHistory::CHARGE) {
                    $itemName = 'DEPOSIT CHARGE';
                    $rate = $row->first()->charge_fee_rate;
                }
                if ($type == ChargeHistory::INTEREST) {
                    $itemName = 'INTEREST';
                    $rate = 0;
                    $interestAmount = $row->sum('payment_amount');
                }

                $incomeDetail = $incomeDetailData->where('type', 3)
                    ->where('client_id', $clientId)
                    ->where('item_name', $itemName)
                    ->first();
                $incomePreviousDetail = $incomePreviousDetailData->where('type', 3)
                    ->where('client_id', $clientId)
                    ->where('item_name', $itemName)
                    ->first();

                if ($incomeDetail) {
                    $incomeDetail->number_transaction = $row->count();
                    $incomeDetail->amount = $row->sum('payment_amount');
                    $incomeDetail->profit = $interestAmount > 0 ? $interestAmount : ceil($row->sum('payment_amount') * $rate / 100);
                    $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                    $incomeDetail->item_name = $itemName;
                    $incomeDetail->save();
                } else {
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => 3,
                        'client_id' => $row->first()->client_id,
                        'rate' => $rate,
                        'number_transaction' => $row->count(),
                        'amount' => $row->sum('payment_amount'),
                        'profit' => $interestAmount > 0 ? $interestAmount : ceil($row->sum('payment_amount') * $rate / 100),
                        'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                        'item_name' => $itemName,
                    ]);
                }
            }
        }

        if (\Illuminate\Support\Carbon::now()->isSameMonth(\Illuminate\Support\Carbon::parse($request->to_date))) {
            $depositToday = $this->getRequestAllClientToday();
            $withdrawalsToday = $this->getWithdrawalAllClientToday(true);

            foreach ($dataClientAggregation as $data) {
                if ($data->type == 1) {
                    $dataToday = $depositToday->where('user_id', $data->client_id);
                    $data->amount += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                    $data->number_trans += $dataToday->isNotEmpty() ? $dataToday->sum('number_of_deposit') : 0;
                    $data->account_usage_fee += $dataToday->isNotEmpty() ? $dataToday->sum('amount') * $data->account_usage_rate / 100 : 0;
                } else {
                    $dataToday = $withdrawalsToday->where('user_id', $data->client_id);
                    $bank = Bank::query()->leftJoin('account', 'account.bank_id', 'bank.id')
                        ->where('account.account_number', '=', $data->account_number)->first();
                    $rate = $bank ? $bank->commission_rate : 0;
                    $dataAccountToday = $dataToday->where('from_account', $data->account_number);
                    if ($data->account_usage_rate == $rate) {
                        $data->amount += $dataAccountToday->isNotEmpty() ? $dataAccountToday->sum('amount') : 0;
                        $data->number_trans += $dataAccountToday->isNotEmpty() ? $dataAccountToday->sum('number_of_withdrawal') : 0;
                        $data->account_usage_fee += $dataAccountToday->isNotEmpty() ? $dataAccountToday->sum('amount') * $data->account_usage_rate / 100 : 0;
                    }
                }

            }
        }
        // generate type expense
        foreach ($dataClientAggregation as $ca) {
            if (!Str::contains($ca->account_number, ['deposit', 'account'])) {
                $this->getReferralFeeByRow($ca);
                $incomeDetail = $incomeDetailData->where('type', 4)
                    ->where('item_name', $ca->account_number)
                    ->where('rate', $ca->account_usage_rate)
                    ->first();
                $incomePreviousDetail = $incomePreviousDetailData->where('type', 4)
                    ->where('item_name', $ca->account_number)
                    ->where('rate', $ca->account_usage_rate)
                    ->first();
                if ($incomeDetail) {
                    $incomeDetail->number_transaction = $ca->number_trans;
                    $incomeDetail->amount = $ca->amount;
                    $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                    $incomeDetail->profit = floor($ca->account_usage_fee);
                    $incomeDetail->save();
                } else {
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => 4,
                        'type_fee' => $ca->type,
                        'client_id' => $ca->client_id,
                        'rate' => $ca->account_usage_rate,
                        'number_transaction' => $ca->number_trans,
                        'amount' => $ca->amount,
                        'profit' => floor($ca->account_usage_fee),
                        'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                        'item_name' => $ca->account_number,
                    ]);
                }
            }
        }


        // generate type expense referal
        $dataDeposit = $dataClientAggregation->where('type', 1)->groupBy('client_id');
        $dataWithdrawal = $dataClientAggregation->where('type', 2)->groupBy('client_id');
        foreach ($dataDeposit as $clientId => $ca) {
            if (((int)floor($ca->sum('referral_fee_client'))) != 0) {
                $incomeDetail = $incomeDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 4)
                    ->where('item_name', 'クライアント紹介手数料')
                    ->first();
                $incomePreviousDetail = $incomePreviousDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 4)
                    ->where('item_name', 'クライアント紹介手数料')
                    ->first();
                if ($incomeDetail) {
                    $incomeDetail->number_transaction = 1;
                    $incomeDetail->amount = $ca->sum('amount');
                    $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                    $incomeDetail->profit = floor($ca->sum('amount') * $incomeDetail->rate / 100);
                    $incomeDetail->save();
                } else {
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => 4,
                        'type_fee' => 4,
                        'client_id' => $clientId,
                        'rate' => $ca->first()->referral_fee_client_rate,
                        'number_transaction' => 1,
                        'amount' => $ca->sum('amount'),
                        'profit' => floor($ca->sum('amount') * $ca->first()->referral_fee_client_rate / 100),
                        'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                        'item_name' => 'クライアント紹介手数料',
                    ]);
                }
            }

            if (((int)floor($ca->sum('referral_fee_account'))) != 0) {
                $incomeDetail = $incomeDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 4)
                    ->where('item_name', '口座紹介手数料')
                    ->first();
                $incomePreviousDetail = $incomePreviousDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 4)
                    ->where('item_name', '口座紹介手数料')
                    ->first();
                if ($incomeDetail) {
                    $incomeDetail->number_transaction = 1;
                    $incomeDetail->amount = $ca->sum('amount');
                    $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                    $incomeDetail->profit = floor($ca->sum('referral_fee_account'));
                    $incomeDetail->save();
                } else {
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => 4,
                        'type_fee' => 4,
                        'client_id' => $clientId,
                        'number_transaction' => 1,
                        'rate' => $ca->first()->referral_fee_account_rate,
                        'amount' => $ca->sum('amount'),
                        'profit' => floor($ca->sum('referral_fee_account')),
                        'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                        'item_name' => '口座紹介手数料',
                    ]);
                }
            }
        }

        foreach ($dataWithdrawal as $clientId => $ca) {
            if (((int)floor($ca->sum('referral_fee_client'))) != 0) {
                $incomeDetail = $incomeDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 5)
                    ->where('item_name', 'クライアント紹介手数料')
                    ->first();
                $incomePreviousDetail = $incomePreviousDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 5)
                    ->where('item_name', 'クライアント紹介手数料')
                    ->first();
                if ($incomeDetail) {
                    $incomeDetail->number_transaction = 1;
                    $incomeDetail->amount = $ca->sum('amount');
                    $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                    $incomeDetail->profit = floor($ca->sum('amount') * $incomeDetail->rate / 100);
                    $incomeDetail->save();
                } else {
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => 4,
                        'type_fee' => 5,
                        'client_id' => $clientId,
                        'rate' => $ca->first()->referral_fee_client_rate,
                        'number_transaction' => 1,
                        'amount' => $ca->sum('amount'),
                        'profit' => floor($ca->sum('amount') * $ca->first()->referral_fee_client_rate / 100),
                        'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                        'item_name' => 'クライアント紹介手数料',
                    ]);
                }
            }

            if (((int)floor($ca->sum('referral_fee_account'))) != 0) {
                $incomeDetail = $incomeDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 5)
                    ->where('item_name', '口座紹介手数料')
                    ->first();
                $incomePreviousDetail = $incomePreviousDetailData->where('type', 4)
                    ->where('client_id', $clientId)
                    ->where('type_fee', 5)
                    ->where('item_name', '口座紹介手数料')
                    ->first();
                if ($incomeDetail) {
                    $incomeDetail->number_transaction = 1;
                    $incomeDetail->amount = $ca->sum('amount');
                    $incomeDetail->previous_month = $incomePreviousDetail ? $incomePreviousDetail->profit : 0;
                    $incomeDetail->profit = floor($ca->sum('referral_fee_account'));
                    $incomeDetail->save();
                } else {
                    IncomeExpenditureDetail::create([
                        'income_expenditure_id' => $incomeExpenditureData->id,
                        'type' => 4,
                        'type_fee' => 5,
                        'client_id' => $clientId,
                        'number_transaction' => 1,
                        'amount' => $ca->sum('amount'),
                        'profit' => floor($ca->sum('referral_fee_account')),
                        'previous_month' => $incomePreviousDetail ? $incomePreviousDetail->profit : 0,
                        'item_name' => '口座紹介手数料',
                    ]);
                }
            }
        }


        $incomeDetailData = IncomeExpenditureDetail::where('income_expenditure_id', $incomeExpenditureData->id)->get();
        $balanceData = $incomeDetailData->where('type', '!=', 4);
        $spendingDate = $incomeDetailData->where('type', 4);
        $incomeExpenditureData->total_balance = $balanceData->sum('profit');
        $incomeExpenditureData->total_spending = $spendingDate->sum('profit');
        $incomeExpenditureData->profit = $balanceData->sum('profit') - $spendingDate->sum('profit');
        $incomeExpenditureData->save();
        $incomeExpenditureData->income_expenditure_details = $incomeDetailData;

        return [
            'income_expenditure' => $incomeExpenditureData
        ];
    }

    public function getReferralFeeByRow($row)
    {
        $listAllIntroducer = IntroducerInformation::all();

        // get introducer rate by client
        $introducerFee = 0;
        $row->referral_fee_client_rate = 0;
        $row->referral_fee_account_rate = 0;
        $introducer = $listAllIntroducer->where('client_id', $row->client_id ?? -1)->first();
        if ($introducer) {
            $row->referral_fee_client_rate = $introducer->referral_fee;
            $introducerFee = $row->amount * $introducer->referral_fee / 100;
        }
        // get account introducer by client
        $accountIntroductionFee = 0;
        $introducer = $listAllIntroducer->where('account_contractor_id', $row->contractor_id ?? -1)->first();
        if ($introducer) {
            $row->referral_fee_account_rate = $introducer->referral_fee;
            $accountIntroductionFee = $row->amount * $introducer->referral_fee / 100;
        }
        $row->referral_fee_client = floor($introducerFee);
        $row->referral_fee_account = floor($accountIntroductionFee);
    }

}
