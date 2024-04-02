<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountBalanceValidation
{
    public function checkAccountBalanceHistoryValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'account_number' => 'required|max:100',
                'date_history' => 'required|date_format:Y-m-d',
                'balance' => 'required|numeric|regex:/^\d{1,10}(?:\.\d)?$/',
                'client_id' => 'required|integer|digits_between:1,11',
            ]
        );
    }
}
