<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceManagementRepositoryInterface;
use Illuminate\Http\Request;

class InvoiceManagementRepository extends BaseRepository implements InvoiceManagementRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return Invoice::class;
    }

    public function getListInvoice(Request $request) {
        return $this->with('invoiceDetails')
            ->where('client_id', '=', $request->client_id)
            ->orderBy('invoice_date', 'desc')->get();
    }

    public function getInvoiceById($invoiceId) {
        return $this->with('invoiceDetails')
            ->where('id', '=', $invoiceId)->get();
    }

    public function getInvoiceDetailForExportPdf(Request $request) {
        return $this->with(['invoiceDetails', 'contrustor', 'client'])
            ->where('id', '=', $request->id)
            ->first()
            ;
    }
}
