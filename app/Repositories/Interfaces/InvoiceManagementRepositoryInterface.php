<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface InvoiceManagementRepositoryInterface extends RepositoryInterface
{
    public function getListInvoice(Request $request);
    public function getInvoiceDetailForExportPdf(Request $request);
}
