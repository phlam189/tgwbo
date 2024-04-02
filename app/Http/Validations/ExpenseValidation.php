<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseValidation
{
    public function checkExpenseValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|numeric',
                'expense_name' => 'required|string|max:10',
                'interest_rate' => 'nullable|numeric|regex:/^\d{1,2}(\.\d)?$/',
            ]
        );
    }
}
