<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientContractDetailValidation
{
    public function checkClientDetailValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => 'required|integer|digits_between:1,11',
                'service_type' => 'nullable|integer|digits_between:1,4',
                'contract_date' => 'nullable|date',
                'contract_rate' => 'nullable|numeric|regex:/^\d{1,2}(\.\d)?$/',
                'is_minimun_charge' => ['required', 'integer', 'digits_between:1,4'],
            ]
        );
    }
}
