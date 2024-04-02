<?php

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\Romaji;
use App\Rules\HalfWidth;

class ClientValidation
{
    public function checkClientValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:500', new Romaji(), new HalfWidth],
                'represent_name' => ['nullable', 'max:500', new Romaji(), new HalfWidth],
                'email' => 'nullable|email|unique:client,email|max:250',
                'address' => ['nullable', 'max:1000', new Romaji(), new HalfWidth],
                'presence' => 'nullable|boolean|min:0|max:4',
                'license_number' => 'min:0|max:50',
                'total_year' => 'nullable|integer|digits_between:1,11',
                'contractor_id' => 'nullable|exists:contructor,id|max:11',
                'client_id' => 'max:11|unique:client,client_id',
                'service_name' => ['required', 'max:500', new Romaji(), new HalfWidth],
                'is_transfer_fee' => 'nullable|integer|digits_between:1,4',
                'charge_fee_rate' => 'nullable|numeric|regex:/^\d{1,2}(\.\d)?$/',
                'settlement_fee_rate' => 'nullable|numeric|regex:/^\d{1,2}(\.\d)?$/',
            ]
        );
    }

    public function checkClientUpdateValidation($id, Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:500', new Romaji(), new HalfWidth],
                'represent_name' => ['nullable', 'max:500', new Romaji(), new HalfWidth],
                'email' => [
                    'nullable',
                    'email',
                    'max:250',
                    Rule::unique('client')->ignore($id),
                ],
                'address' => ['nullable', 'max:1000', new Romaji(), new HalfWidth],
                'presence' => 'nullable|boolean|min:0|max:4',
                'license_number' => 'min:0|max:50',
                'total_year' => 'nullable|integer|digits_between:1,11',
                'contractor_id' => 'nullable|exists:contructor,id|max:11',
                'client_id' => 'max:11', Rule::unique('client')->ignore($id),
                'service_name' => ['required', 'max:500', new Romaji(), new HalfWidth],
                'is_transfer_fee' => 'nullable|integer|digits_between:1,4',
                'charge_fee_rate' => 'nullable|numeric|regex:/^\d{0,2}(\.\d)?$/',
                'settlement_fee_rate' => 'nullable|numeric|regex:/^\d{0,2}(\.\d)?$/',
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

    public function checkUniqueClientIdValidation($request)
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => [
                    'required',
                ],
            ]
        );
    }
}