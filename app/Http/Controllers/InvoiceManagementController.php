<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Validations\InvoiceManagementValidation;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Services\InvoiceManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
class InvoiceManagementController extends Controller
{
    private InvoiceManagementService $invoiceManagementService;
    private InvoiceManagementValidation $invoiceManagementValidation;

    public function __construct(
        InvoiceManagementService $invoiceManagementService,
        InvoiceManagementValidation $invoiceManagementValidation
    )
    {
        $this->invoiceManagementService = $invoiceManagementService;
        $this->invoiceManagementValidation = $invoiceManagementValidation;
    }
    public function index(Request $request)
    {
        $validator = $this->invoiceManagementValidation->checkInoivceManagementGetListValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }
        $invoices = $this->invoiceManagementService->getListInvoice($request);

        return response()->json($invoices);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->invoiceManagementValidation->checkInoivceManagementStoreValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }

        $invoiceData = Arr::except($request->all(), 'invoice_details');
        $invoiceDetailsData = $request->all()['invoice_details'];

        $invoice = Invoice::create($invoiceData);

        foreach ($invoiceDetailsData as $detailData) {
            $detailData['invoice_id'] = $invoice->id;
            InvoiceDetail::create($detailData);
        }

        return response()->json($invoice->load('invoiceDetails'), 201);
    }

    public function show(Invoice $invoice): \Illuminate\Http\JsonResponse
    {
        $invoice = $this->invoiceManagementService->getInvoiceById($invoice->id);
        return response()->json($invoice);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->invoiceManagementValidation->checkInoivceManagementUpdateValidation(
            $request
        );
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }

        $invoiceData = Arr::except($request->all(), 'invoice_details');
        $invoiceDetailsData = $request->all()['invoice_details'];
        $invoice = Invoice::find($request->id);
        $invoiceCreate = $invoice->update($invoiceData);

        foreach ($invoiceDetailsData as $detailData) {
            if (isset($detailData['id'])) {
                $invoiceDetail = InvoiceDetail::find($detailData['id']);
                if ($invoiceDetail && $invoiceDetail->invoice_id == $invoice->id) {
                    $invoiceDetail->update($detailData);
                }
            } else {
                $detailData['invoice_id'] = $invoice->id;
                InvoiceDetail::create($detailData);
            }
        }

        // Remove InvoiceDetail records not present in the input data
//        $inputInvoiceDetailIds = array_filter(array_column($invoiceDetailsData, 'id'));
//        $invoice->invoiceDetails()->whereNotIn('id', $inputInvoiceDetailIds)->delete();

        return response()->json($invoice->load('invoiceDetails'));
    }

    public function destroy(Invoice $invoice): \Illuminate\Http\JsonResponse
    {
        $invoice->delete();
        return response()->json(null, 204);
    }

    public function exportPdf(Request $request): \Illuminate\Http\JsonResponse {
        $invoice = $this->invoiceManagementService->getInvoiceDetailForExportPdf($request);
        $depositDetails = ['sumTotalAmountMoney' => 0, 'sumTotalTransaction' => 0, 'items' => []];
        $withdrawalDetails = ['sumTotalAmountMoney' => 0, 'sumTotalTransaction' => 0, 'items' => []];
        $otherDetails = ['sumTotalAmountMoney' => 0, 'sumTotalTransaction' => 0, 'items' => []];

        // dd($invoice->invoiceDetails->toArray());
        if ($invoice == null){
            throw new BusinessException("EUA000");
        }

        foreach ($invoice->invoiceDetails AS $detail) {
            $totalAmount = '¥' . number_format($detail->total_amount, 0, '.', ',');
            $systemUsageFee = '¥' . number_format($detail->system_usage_fee, 0, '.', ',');

            $rate = number_format($detail->rate, 2);
            $rate = rtrim($rate, '0');
            $rate = rtrim($rate, '.');
            $rate = $rate . '%';

            if ($detail->type == '1') {//Deposit
                if ($depositDetails['sumTotalTransaction'] == 0 && $detail->number_transaction > 0)
                    $depositDetails['sumTotalTransaction'] = $detail->number_transaction;

                $depositDetails['sumTotalAmountMoney'] += (float)$detail->total_amount;
                $depositDetails['items'][] = Array(
                    "description" => $detail->description,
                    "qty" => '',
                    "rate" => $rate,
                    "total_amount" => $totalAmount,
                    "system_usage_fee" => $systemUsageFee,
                );
            } else if ($detail->type == '2') {//withdrawal
                if ($withdrawalDetails['sumTotalTransaction'] == 0 && $detail->number_transaction > 0)
                    $withdrawalDetails['sumTotalTransaction'] = $detail->number_transaction;

                $withdrawalDetails['sumTotalAmountMoney'] += (float)$detail->total_amount;
                $withdrawalDetails['items'][] = Array(
                    "description" => $detail->description,
                    "qty" => '',
                    "rate" => $rate,
                    "total_amount" => $totalAmount,
                    "system_usage_fee" => $systemUsageFee,
                );
            } else if ($detail->type == '3') {//Other
                $otherDetails['items'][] = Array(
                    "description" => $detail->description,
                    "qty" => '',
                    "rate" => $rate,
                    "total_amount" => $totalAmount,
                    "system_usage_fee" => $systemUsageFee,
                );
            }
        }

        if(count($depositDetails['items']) == 1){
            $dataPdf[] = Array(
                "description" => "【Deposit】",
                "rate" => $depositDetails['items'][0]['rate'],
                "qty" => number_format($depositDetails["sumTotalTransaction"], 0, '.', ','),
                "total_amount" => number_format($depositDetails['sumTotalAmountMoney'], 0, '.', ','),
                "system_usage_fee" => $depositDetails['items'][0]['system_usage_fee'],
            );
        }
        else {
            $dataPdf[] = Array(
                "description" => "【Deposit】",
                "rate" => "-",
                "qty" => number_format($depositDetails["sumTotalTransaction"], 0, '.', ','),
                "total_amount" => number_format($depositDetails['sumTotalAmountMoney'], 0, '.', ','),
                "system_usage_fee" => "-",
            );
            $dataPdf = array_merge($dataPdf, $depositDetails['items']);
        }

        if(count($withdrawalDetails['items']) == 1){
            $dataPdf[] = Array(
                "description" => "【Withdrawal】",
                "rate" => $withdrawalDetails['items'][0]['rate'],
                "qty" => number_format($withdrawalDetails["sumTotalTransaction"], 0, '.', ','),
                "total_amount" => number_format($withdrawalDetails['sumTotalAmountMoney'], 0, '.', ','),
                "system_usage_fee" => $withdrawalDetails['items'][0]['system_usage_fee'],
            );
        }
        else {
            $dataPdf[] = Array(
                "description" => "【Withdrawal】",
                "rate" => "-",
                "qty" => number_format($withdrawalDetails["sumTotalTransaction"], 0, '.', ','),
                "total_amount" => number_format($withdrawalDetails['sumTotalAmountMoney'], 0, '.', ','),
                "system_usage_fee" => "-",
            );
            $dataPdf = array_merge($dataPdf, $withdrawalDetails['items']);
        }

        $dataPdf = array_merge($dataPdf, $otherDetails['items']);

        $invoiceTaxRate = number_format($invoice->tax_rate, 1);
        $invoiceTaxRate = rtrim($invoiceTaxRate, '0');
        $invoiceTaxRate = rtrim($invoiceTaxRate, '.');

        $data = [
            'invoice' => $invoice,
            'contrustor' => $invoice->contrustor,
            'dataPdf' => $dataPdf,
            'client' => $invoice->client,
            'invoiceTaxRate' => $invoiceTaxRate
        ];

        $date = date('YmdHis', time());

        $fileName = $invoice->invoice_no . '_' . $date . '.pdf';

        $invoice_path = config('filesystems.invoice');

        $filePath = $invoice_path . '/' . $fileName;

        $pdf = Pdf::loadView('pdf.invoice', $data)->setPaper('a4');

        $content = $pdf->download()->getOriginalContent();
        Storage::disk('public')->put($filePath, $content);

        $url = config('app.url') . '/download?filename=' . $fileName;

        return response()->json(
            [
                'url' => $url,
            ],
            200
        );
    }
}
