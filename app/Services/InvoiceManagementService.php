<?php

namespace App\Services;

use App\Repositories\Interfaces\InvoiceManagementRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceManagementService
{
    public InvoiceManagementRepositoryInterface $invoiceManagementRepository;
    public ClientAggregationService $clientAggregationService;

    public function __construct(
        InvoiceManagementRepositoryInterface $invoiceManagementRepository,
        ClientAggregationService $clientAggregationService
    )
    {
        $this->invoiceManagementRepository = $invoiceManagementRepository;
        $this->clientAggregationService = $clientAggregationService;
    }

    public function getListInvoice(Request $request) {
        return $this->invoiceManagementRepository->getListInvoice($request);
    }

    public function getInvoiceById($invoiceId) {
        return $this->invoiceManagementRepository->getInvoiceById($invoiceId);
    }


    public function getInvoiceDetailForExportPdf(Request $request) {
        return $this->invoiceManagementRepository->getInvoiceDetailForExportPdf($request);
    }


}
