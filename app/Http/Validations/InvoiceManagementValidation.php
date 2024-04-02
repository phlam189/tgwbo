<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceManagementValidation
{
    public function checkInoivceManagementGetListValidation(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => 'required|integer',
            ]
        );
    }
    public function checkInoivceManagementStoreValidation(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => 'required|integer',
                'contractor_id' => 'required|integer',
                'invoice_no' => 'required',
                'invoice_date' => 'required',
                'due_date' => 'required',
                'sub_total' => 'required',
                'discount_amount' => 'required',
                'tax_rate' => 'required',
                'total_tax' => 'required',
                'balance' => 'required',
                // Add validation for other Invoice fields as needed
                'invoice_details' => 'required|array',
                'invoice_details.*.type' => 'required|string',
                'invoice_details.*.description' => 'nullable|string',
                'invoice_details.*.rate' => 'required|numeric',
                'invoice_details.*.number_transaction' => 'required|integer',
                'invoice_details.*.system_usage_fee' => 'required|numeric',
                'invoice_details.*.total_amount' => 'required|numeric',
            ]
        );
    }

    public function checkInoivceManagementUpdateValidation(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'id' => 'required|integer',
                'client_id' => 'required|integer',
                'contractor_id' => 'required|integer',
                'invoice_no' => 'required',
                'invoice_date' => 'required',
                'due_date' => 'required',
                'sub_total' => 'required',
                'discount_amount' => 'required',
                'tax_rate' => 'required',
                'total_tax' => 'required',
                'balance' => 'required',
                // Add validation for other Invoice fields as needed
                'invoice_details' => 'required|array',
                'invoice_details.*.type' => 'required|string',
                'invoice_details.*.description' => 'nullable|string',
                'invoice_details.*.rate' => 'required|numeric',
                'invoice_details.*.number_transaction' => 'required|integer',
                'invoice_details.*.system_usage_fee' => 'required|numeric',
                'invoice_details.*.total_amount' => 'required|numeric',
            ]
        );
    }
}
