<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChargeHistoryValidation
{
    public function checkChargeHistoryValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => 'required|integer|digits_between:1,11',
                'type' => 'required|integer|digits_between:1,4',
                'payment_amount' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) {
                        if ($value >= -999999999 && $value <= 999999999) {
                            return;
                        }
                        $fail($attribute . ' must be a number a maximum of 9 digits.');
                    },
                ],
                'charge_fee' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) {
                        if ($value >= -999999999 && $value <= 999999999) {
                            return;
                        }
                        $fail($attribute . ' must be a number a maximum of 9 digits.');
                    },
                ],
                'create_date' => 'required',
            ]
        );
    }

    public function checkUpdateChargeHistoryValidation($id, Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => 'required|integer|digits_between:1,11',
                'type' => 'required|integer|digits_between:1,4',
                'payment_amount' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) {
                        if ($value >= -999999999 && $value <= 999999999) {
                            return;
                        }
                        $fail($attribute . ' must be a number a maximum of 9 digits.');
                    },
                ],
                'charge_fee' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) {
                        if ($value >= -999999999 && $value <= 999999999) {
                            return;
                        }
                        $fail($attribute . ' must be a number a maximum of 9 digits.');
                    },
                ],
                'memo' => 'min:0',
                'create_date' => 'required',
            ]
        );
    }
}
