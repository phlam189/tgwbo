<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientAggregationValidation
{
    public function checkClientAggregationValidation(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'from_date' => 'required|date_format:Y-m-d H:i:s',
                'to_date' => 'required|date_format:Y-m-d H:i:s',
            ]
        );
    }
}
