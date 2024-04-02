<?php

namespace App\Http\Validations;

use App\Enums\SocialType;
use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class Validation
{
    public function checkValidation($validator)
    {
        if ($validator->fails()) {
            throw new BusinessException($validator->errors()->first());
        }
    }
}
