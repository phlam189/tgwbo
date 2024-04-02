<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountValidation
{
    public function checkAccountValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'contractor_id' => 'required|min:0|max:11',
                'bank_id' => 'required|numeric|digits_between:1,11',
                'service_type' => 'required|numeric|digits_between:1,4',
                'category_name' => 'required',
                'bank_name' => 'required',
                'branch_name' => 'required|max:10',
                'representative_account' => 'max:7',
                'account_number' => 'required|max:7|unique:account,account_number',
                'account_holder' => 'required|max:20',
                'commission_rate' => ['numeric', 'regex:/^\d{1,2}(\.\d)?$/'],
                'branch_code' => ['required', 'regex:/^[0-9]+$/']
            ]
        );
    }

    public function checkUpdateAccountValidation(Request $request, $id)
    {
        return Validator::make(
            $request->all(),
            [
                'user_edit_id' => 'required|min:0|max:11',
                'contractor_id' => 'required|min:0|max:11',
                'bank_id' => 'required|numeric|digits_between:1,11',
                'service_type' => 'required|numeric|digits_between:1,4',
                'category_name' => 'required',
                'bank_name' => 'required',
                'branch_name' => 'required|max:10',
                'representative_account' => 'max:7',
                'account_number' => 'required', 'max:7', Rule::unique('account')->ignore($id),
                'account_holder' => 'required|max:20',
                'commission_rate' => ['numeric', 'regex:/^\d{1,2}(\.\d)?$/'],
                'branch_code' => ['required', 'regex:/^[0-9]+$/']
            ]
        );
    }

    public function checkGetListAccountIdValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'account_number' => 'required|integer',
            ]
        );
    }

    public function checkGetAccountNumberByClient(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'client_id' => 'required',
            ]
        );
    }

    public function checkUniqueAccountNumberValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'account_number' => 'required|numeric',
            ]
        );
    }
}
