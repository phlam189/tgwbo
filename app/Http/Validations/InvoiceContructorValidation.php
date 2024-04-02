<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceContructorValidation
{
    public function checkInvoiceContructorValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'invoice_date' => 'required|date',
                'contructor_id' => 'required',
                'number' => 'nullable|max:15'
            ]
        );
    }

    public function checkUpdateInvoiceContructorValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'invoice_date' => 'required|date',
                'number' => 'nullable|max:15'
            ]
        );
    }
}
