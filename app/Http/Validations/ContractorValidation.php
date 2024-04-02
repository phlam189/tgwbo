<?php

namespace App\Http\Validations;

use App\Exceptions\BusinessException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Rules\Romaji;

class ContractorValidation
{
    public function checkContractorValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:500', new Romaji()],
                'manager' => ['nullable', 'max:250', new Romaji()],
                'email' => 'required|email|unique:contructor,email|max:250',
                'address' => ['max:1000', new Romaji()],
                'invoice_prefix' => 'min:0|max:2',
            ]
        );
    }

    public function checkAccountContractorValidation(Request $request)
    {
        $valid = [
            'user_edit_id' => 'required|min:0|max:11',
            'company_name' => ['required', 'max:500', new Romaji()],
            'address' => ['max:1000', new Romaji()],
            'company_type' => 'required',
            'representative_name' => ['required', 'max:250', new Romaji()],
            'date_of_birth' => 'required|date_format:Y-m-d',
            'presence' => 'required',
            'existence' => 'required',
        ];

        if ($request->email != null and $request->email != "")
            $valid['email'] = 'required|email|unique:contructor,email|max:250';

        return Validator::make(
            $request->all(), $valid
        );
    }

    public function checkContractorUpdateValidation($id, Request $request)
    {
        $emailValid = [
            'max:250',
            Rule::unique('contructor')->ignore($id)
        ];

        if ($request->email != null and $request->email != "")
            $emailValid[] = 'email';

        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:500', new Romaji()],
                'manager' => ['nullable', 'max:250', new Romaji()],
                'email' => $emailValid,
                'address' => ['max:1000', new Romaji()],
                'invoice_prefix' => 'min:0|max:2',
            ]
        );
    }

    public function checkAccountContractorUpdateValidation($id, Request $request)
    {
        $emailValid = [
            'max:250',
            Rule::unique('contructor')->ignore($id)
        ];

        if ($request->email != null and $request->email != "")
            $emailValid[] = 'email';

        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'company_name' => ['required', 'max:500', new Romaji()],
                'email' => $emailValid,
                'address' => ['max:1000', new Romaji()],
                'company_type' => 'required',
                'representative_name' => ['required', 'max:250', new Romaji()],
                'date_of_birth' => 'required|date_format:Y-m-d',
                'presence' => 'required',
                'existence' => 'required',
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
                    'email',
                    'max:250'
                ],
            ]
        );
    }
}
