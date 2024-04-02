<?php

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\HalfWidth;

class IntroducerInformationvalidation
{
    public function checkIntroducerInformationValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:50', new HalfWidth],
                'representative_name' => ['nullable', 'max:30', new HalfWidth],
                'email' => 'nullable|email:rfc,dns|unique:introducer_infomation,email|max:50',
                'address' => ['max:100', new HalfWidth],
                'contractor_id' => 'nullable|exists:contructor,id|max:11',
                'presence' => 'required|boolean|max:4',
                'referral_classification' => 'required|max:4',
                'referral_fee' => 'nullable|numeric|regex:/^\d{0,2}(\.\d)?$/',
                'contract_date' => 'nullable|date_format:Y-m-d',
            ]
        );
    }
    public function checkIntroducerInformationUpdateValidation($id, Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:50', new HalfWidth],
                'representative_name' => ['nullable', 'max:30', new HalfWidth],
                'email' => [
                    'nullable',
                    'email:rfc,dns',
                    'max:250',
                    Rule::unique('introducer_infomation')->ignore($id),
                ],
                'address' => ['max:100', new HalfWidth],
                'contractor_id' => 'nullable|exists:contructor,id|max:11',
                'presence' => 'required|boolean|max:4',
                'referral_classification' => 'required|max:4',
                'referral_fee' => 'nullable|numeric|regex:/^\d{0,2}(\.\d)?$/',
                'contract_date' => 'nullable|date_format:Y-m-d',
            ]
        );
    }

    public function checkUniqueEmailValidation($request)
    {
        return Validator::make(
            $request->all(),
            [
                'email' => [
                    'nullable',
                    'max:250'
                ],
            ]
        );
    }
}
