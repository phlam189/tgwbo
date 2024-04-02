<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Validations\ClientAggregationValidation;
use App\Models\Bank;
use App\Models\Client;
use App\Models\InvoiceContructor;
use App\Services\ClientAggregationService;
use App\Services\ClientService;
use App\Services\ContractorService;
use App\Services\InvoiceContructorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use PDF;

class ClientAggregationController extends Controller
{
    private ClientAggregationService $clientAggregationService;
    private ClientAggregationValidation $clientAggregationValidation;
    private ContractorService $contractorService;
    private ClientService $clientService;
    private InvoiceContructorService $invoiceContructorService;

    public function __construct(
        ClientAggregationService $clientAggregationService,
        ClientAggregationValidation $clientAggregationValidation,
        ContractorService $contractorService,
        ClientService $clientService,
        InvoiceContructorService $invoiceContructorService
    ) {
        $this->clientAggregationService = $clientAggregationService;
        $this->clientAggregationValidation = $clientAggregationValidation;
        $this->contractorService = $contractorService;
        $this->clientService = $clientService;
        $this->invoiceContructorService = $invoiceContructorService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getTransaction(Request $request)
    {
        if (!$request->page) {
            $validator = $this->clientAggregationValidation->checkClientAggregationValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
        }

        if ($request->group_by_year) {
            $request->from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfYear()->format('Y-m-d H:i:s');
            if (Carbon::parse($request->to_date)->isSameYear(Carbon::now())) {
                $request->to_date = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
            } else {
                $request->to_date = Carbon::parse($request->to_date)->endOfYear()->format('Y-m-d H:i:s');
            }

        }else if ($request->group_by_month){
            $request->from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfMonth()->format('Y-m-d H:i:s');
            $request->to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfMonth()->format('Y-m-d H:i:s');
        }else {
            $request->from_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $request->to_date = Carbon::createFromFormat('Y-m-d H:i:s', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
        }

        $transactionsList = $this->clientAggregationService->getTransaction($request);

        $depositList = $transactionsList->where('type', 1)->groupBy('client_id');
        $withdrawalsList = $transactionsList->where('type', 2)->groupBy('client_id');
        $hasDataToday = $request->is_sum;

        if ($depositList->isEmpty() && $withdrawalsList->isEmpty() && Carbon::now()->lt(Carbon::parse($request->to_date))) {
            if (!$request->csv) {
                if ($request->client_id) {
                    $listClient = Client::all(['client_id', 'represent_name', 'system_usage_rate'])->where('client_id', $request->client_id)->groupBy('client_id');
                    $listClient2 = Client::all(['client_id', 'represent_name', 'system_usage_rate'])->where('client_id', $request->client_id)->groupBy('client_id');
                } else {
                    $listClient = Client::all(['client_id', 'represent_name', 'system_usage_rate'])->groupBy('client_id');
                    $listClient2 = Client::all(['client_id', 'represent_name', 'system_usage_rate'])->groupBy('client_id');
                }

                $depositList = $listClient;
                $withdrawalsList = $listClient2;
                $today = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
                $hasDataToday = true;

                foreach ($listClient as $key => $client) {
                    $depositList[$key]->first()->type = 1;
                    $depositList[$key]->first()->date = $today;
                    $withdrawalsList[$key]->first()->type = 2;
                    $withdrawalsList[$key]->first()->date = $today;

                }
            }
        }


        if (\Illuminate\Support\Carbon::now()->isSameMonth(Carbon::parse($request->to_date))
            && Carbon::now()->lt(Carbon::parse($request->to_date))) {
            $depositToday = $this->clientAggregationService->getRequestAllClientToday();
            $withdrawalToday = $this->clientAggregationService->getWithdrawalAllClientToday();
            $todayDate = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
            if (!$request->csv) {
                foreach ($depositList as $clientId => $row) {
                    $client = Client::where('client_id', $clientId)->first();
                    $systemRate = $client ? $client->system_usage_rate : 0;
                    if ((!$hasDataToday && !$request->group_by_year)
                        || ($request->group_by_year && !$request->is_sum && Carbon::now()->startOfDay()->eq(Carbon::now()->startOfMonth()))) {
                        $row->prepend((object)[
                            "amount" => 0,
                            "number_trans" => 0,
                            "account_balance" => 0,
                            "actual_balance" => 0,
                            "type" => 1,
                            "date" => $todayDate,
                            "system_usage_rate" => $systemRate
                        ]);
                    }
                    foreach ($depositToday as $today) {
                        if ($today->user_id == $clientId) {
                            $row->first()->amount = $row->first()->amount + $today->amount;
                            $row->first()->system_usage_rate = $systemRate;
                            $row->first()->system_usage_fee = ceil($row->first()->amount + $today->amount * $systemRate / 100);
                            $row->first()->number_trans = $row->first()->number_trans + $today->number_of_deposit;
                            $row->first()->account_balance = $row->first()->account_balance + $today->amount;
                            $row->first()->actual_balance = $row->first()->actual_balance ?? 0 + $today->amount;
                        }
                    }
                }

                foreach ($withdrawalsList as $clientId => $row) {
                    if ((!$hasDataToday && !$request->group_by_year)
                        || ($request->group_by_year && !$request->is_sum && Carbon::now()->startOfDay()->eq(Carbon::now()->startOfMonth()))) {
                        $row->prepend((object)[
                            "amount" => 0,
                            "number_trans" => 0,
                            "account_balance" => 0,
                            "actual_balance" => 0,
                            "type" => 2,
                            "date" => $todayDate,
                            "system_usage_rate" => $systemRate
                        ]);
                    }
                    foreach ($withdrawalToday as $today) {
                        if ($today->user_id == $clientId) {
                            $row->first()->amount = $row->first()->amount + $today->amount;
                            $row->first()->system_usage_rate = $systemRate;
                            $row->first()->system_usage_fee = ceil($row->first()->amount + $today->amount * $systemRate / 100);
                            $row->first()->number_trans = $row->first()->number_trans + $today->number_of_withdrawal;
                            $row->first()->account_balance = $row->first()->account_balance - $today->amount;
                            $row->first()->actual_balance = $row->first()->actual_balance ?? 0 - $today->amount;
                        }
                    }
                }
            }
        }

        foreach ($depositList as $key => $item) {
            $this->clientAggregationService->getAccountBalance($item ?? collect([]), $key, $request);
            $this->clientAggregationService->getChargeHistory($item ?? collect([]), $key, $request);
        }

        foreach ($withdrawalsList as $key => $item) {
            $this->clientAggregationService->getAccountBalance($item ?? collect([]), $key, $request);
            $this->clientAggregationService->getChargeHistory($item ?? collect([]), $key, $request);
        }

        $dataToday = [];
        if ($request->client_id && $request->include_today) {
            $dataToday = $this->clientAggregationService->getSummaryClientAggregation($request);
        }
        $depositAggregationByClient = $this->clientAggregationService->aggregationByClient($depositList, $request, $dataToday);
        $withdrawalsAggregationByClient = $this->clientAggregationService->aggregationByClient($withdrawalsList, $request, $dataToday);

        $depositListDate = [];
        $withdrawalsListDate = [];
        if ($request->group_by_year) {
            if ($request->client_id) {
                $client = Client::where('client_id', $request->client_id)->first();
                $systemRate = $client ? $client->system_usage_rate : 0;
                $transactionsList = $this->clientAggregationService->getTransactionByDate($request);
                $depositListDate = $transactionsList->where('type', 1)->groupBy('client_id');
                $withdrawalsListDate = $transactionsList->where('type', 2)->groupBy('client_id');
                $todayDate = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');

                if (!$request->csv) {
                    foreach ($depositListDate as $clientId => $row) {
                        $row->prepend((object)[
                            "amount" => 0,
                            "number_trans" => 0,
                            "account_balance" => 0,
                            "actual_balance" => 0,
                            "type" => 1,
                            "date" => $todayDate,
                            "system_usage_rate" => $systemRate
                        ]);
                        foreach ($depositToday as $today) {
                            if ($today->user_id == $clientId) {
                                $row->first()->amount = $row->first()->amount + $today->amount;
                                $row->first()->system_usage_rate = $systemRate;
                                $row->first()->system_usage_fee = ceil($row->first()->amount + $today->amount * $systemRate / 100);
                                $row->first()->number_trans = $row->first()->number_trans + $today->number_of_deposit;
                                $row->first()->account_balance = $row->first()->account_balance + $today->amount;
                                $row->first()->actual_balance = $row->first()->actual_balance ?? 0 + $today->amount;
                            }
                        }
                    }

                    foreach ($withdrawalsListDate as $clientId => $row) {
                        $row->prepend((object)[
                            "amount" => 0,
                            "number_trans" => 0,
                            "account_balance" => 0,
                            "actual_balance" => 0,
                            "type" => 2,
                            "date" => $todayDate,
                            "system_usage_rate" => $systemRate
                        ]);
                        foreach ($withdrawalToday as $today) {
                            if ($today->user_id == $clientId) {
                                $row->first()->amount = $row->first()->amount + $today->amount;
                                $row->first()->system_usage_rate = $systemRate;
                                $row->first()->system_usage_fee = ceil($row->first()->amount + $today->amount * $systemRate / 100);
                                $row->first()->number_trans = $row->first()->number_trans + $today->number_of_withdrawal;
                                $row->first()->account_balance = $row->first()->account_balance - $today->amount;
                                $row->first()->actual_balance = $row->first()->actual_balance ?? 0 - $today->amount;
                            }
                        }
                    }
                }

                $this->clientAggregationService->getAccountBalance($depositListDate[$request->client_id] ?? collect([]), $request->client_id, $request);
                $this->clientAggregationService->getAccountBalance($withdrawalsListDate[$request->client_id] ?? collect([]), $request->client_id, $request);
                $this->clientAggregationService->getChargeHistory($depositListDate[$request->client_id] ?? collect([]), $request->client_id, $request);
                $this->clientAggregationService->getChargeHistory($withdrawalsListDate[$request->client_id] ?? collect([]), $request->client_id, $request);
                $depositAggregationByClient = $this->clientAggregationService->aggregationByClient($depositListDate, $request, $dataToday);
                $withdrawalsAggregationByClient = $this->clientAggregationService->aggregationByClient($withdrawalsListDate, $request, $dataToday);
            }
        }

        if ($request->csv) {
            if ($request->group_by_year) {
                return $this->clientAggregationService->exportCsv($depositListDate, $withdrawalsListDate, $request);
            } else {
                return $this->clientAggregationService->exportCsv($depositList, $withdrawalsList, $request);
            }
        }


        return [
            'deposit' => $depositList,
            'withdrawals' => $withdrawalsList,
            'deposit_for_each_date' => $depositListDate,
            'withdrawals_for_each_date' => $withdrawalsListDate,
            'today' => $dataToday,
            'deposit_aggregation_by_client' => $depositAggregationByClient,
            'withdrawals_aggregation_by_client' => $withdrawalsAggregationByClient,
        ];
    }

    public function getAccountUsageFee(Request $request)
    {
        $validator = $this->clientAggregationValidation->checkClientAggregationValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }

        $transactionsList = $this->clientAggregationService->getAccountUsageFee($request);
        $depositList = $transactionsList->where('type', 1)->groupBy('client_id');
        $withdrawalsList = $transactionsList->where('type', 2)->groupBy('client_id');

        if (\Illuminate\Support\Carbon::now()->isSameMonth(\Illuminate\Support\Carbon::parse($request->to_date))) {
            $depositToday = $this->clientAggregationService->getRequestAllClientToday();
            $withdrawalsToday = $this->clientAggregationService->getWithdrawalAllClientToday(true);

            foreach ($depositList as $clientId => $data) {
                $dataToday = $depositToday->where('user_id', $clientId);
                $data->first()->amount += $dataToday->isNotEmpty() ? $dataToday->sum('amount') : 0;
                $data->first()->number_trans += $dataToday->isNotEmpty() ? $dataToday->sum('number_of_deposit') : 0;
                $data->first()->account_usage_fee += $dataToday->isNotEmpty() ? $dataToday->sum('amount') * $data->first()->account_usage_rate / 100 : 0;
            }

            foreach ($withdrawalsList as $clientId => $data) {
                $dataToday = $withdrawalsToday->where('user_id', $clientId);
                $dataByAccountNumber = $data->groupBy('account_number');
                foreach ($dataByAccountNumber as $accountNumber => $row) {
                    $bank = Bank::query()->leftJoin('account', 'account.bank_id', 'bank.id')
                        ->where('account.account_number', '=', $accountNumber)->first();
                    $rate = $bank ? $bank->commission_rate : 0;
                    $rowAccountFee = $row->where('account_usage_rate', $rate);
                    $dataAccountToday = $dataToday->where('from_account', $accountNumber);
                    if ($rowAccountFee->isNotEmpty()) {
                        $rowAccountFee->first()->amount += $dataAccountToday->isNotEmpty() ? $dataAccountToday->sum('amount') : 0;
                        $rowAccountFee->first()->number_trans += $dataAccountToday->isNotEmpty() ? $dataAccountToday->sum('number_of_withdrawal') : 0;
                        $rowAccountFee->first()->account_usage_fee += $dataAccountToday->isNotEmpty() ? $dataAccountToday->sum('amount') * $data->first()->account_usage_rate / 100 : 0;
                    }
                }
            }

        }

        $depositAggregationByClient = $this->clientAggregationService->aggregationByClient($depositList, $request);
        $withdrawalsAggregationByClient = $this->clientAggregationService->aggregationByClient($withdrawalsList, $request);


        return [
            'deposit' => $depositList,
            'deposit_aggregation_by_client' => $depositAggregationByClient,
            'withdrawals' => $withdrawalsList,
            'withdrawals_aggregation_by_client' => $withdrawalsAggregationByClient,
        ];
    }

    public function export_pdf(Request $request)
    {
        $validator = $this->clientAggregationValidation->checkClientAggregationValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }
        $transactionsList = $this->clientAggregationService->getAccountUsageFee($request);
        $depositList = $transactionsList->where('type', 1)->groupBy('client_id');
        $withdrawalsList = $transactionsList->where('type', 2)->groupBy('client_id');
        $contractorIsHonsha = $this->contractorService->findContractorIsHonsha($request->contractor_id);
        $contractor = $this->contractorService->findContractor($request->contractor_id);
        $depositListWithdraw = [
            'deposit' => $depositList->toArray(),
            'withdrawal' => $withdrawalsList->toArray(),
        ];
        $invoiceContructor = $this->invoiceContructorService->findByNumber($request);
        if (!empty($depositListWithdraw['deposit']) || !empty($depositListWithdraw['withdrawals'])) {
            $data = [
                'depositListWithdraw' => $depositListWithdraw,
                'contructor' => $contractor,
                'contractorIsHonsha' => $contractorIsHonsha,
                'invoiceContructor' => $invoiceContructor,
                'lang' => $request['lang'] ?? 'en'
            ];
            $date = date('YmdHis', time());

            $fileName = 'account_fee_' . ($contractor->id ?? 0) . '_' . $date . '.pdf';

            $invoice_path = config('filesystems.invoice');

            $filePath = $invoice_path . '/' . $fileName;

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.account_fee', $data)->setPaper('a4');

            $content = $pdf->download()->getOriginalContent();
            Storage::disk('public')->put($filePath, $content);

            $url = config('app.url') . '/download?filename=' . $fileName;

            return response()->json([
                'download_link' => $url
            ]);
        }
    }

    public function getSummaryClientAggregation(Request $request)
    {
        $validator = $this->clientAggregationValidation->checkClientAggregationValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }

        $transactionsList = $this->clientAggregationService->getSummaryClientAggregation($request);

        return [
            'today' => $transactionsList
        ];
    }

    public function getInComeAndExpenditure(Request $request)
    {
        $validator = $this->clientAggregationValidation->checkClientAggregationValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }

        return $this->clientAggregationService->getInComeAndExpenditure($request);
    }

    public function getSummaryIncomeExpenditure(Request $request)
    {
        $validator = $this->clientAggregationValidation->checkClientAggregationValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }

        return $this->clientAggregationService->getSummaryIncomeExpenditure($request);
    }
}
