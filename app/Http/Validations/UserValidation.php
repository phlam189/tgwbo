<?php

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class UserValidation
{
    public function checkRegisterValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'email' => 'required',
            ],
            [
                'email.required' => 'EUA_001',
            ]
        );
    }

    public function getTokenValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'role' => 'required'
            ],
            [
                'email.required' => 'EUA_001',
                "email.email" => "EUA_003",
            ]
        );
    }
}
