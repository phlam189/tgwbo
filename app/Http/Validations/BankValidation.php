<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankValidation
{
    public function checkBankValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'bank_name' => 'required|max:250',
                'client_withdrawal_fee_1' => 'required|integer|digits_between:1,3',
                'client_withdrawal_fee_2' => 'required|integer|digits_between:1,3',
                'contract_withdrawal_fee_1' => 'nullable|integer|digits_between:1,3',
                'contract_withdrawal_fee_2' => 'nullable|integer|digits_between:1,3',
                'bank_code' => 'required|integer',
                'difference_fee' => 'numeric',
            ]
        );
    }
}
