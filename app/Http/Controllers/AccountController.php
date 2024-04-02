<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Validations\AccountValidation;
use App\Models\Account;
use App\Models\User;
use App\Services\AccountService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    protected $accountValidation;
    protected $accountService;
    protected $logActionHistoryService;

    public function __construct(AccountValidation $accountValidation, AccountService $accountService, LogActionHistoryService $logActionHistoryService)
    {
        $this->accountValidation = $accountValidation;
        $this->accountService = $accountService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index()
    {
        $accounts = $this->accountService->getList();
        return response()->json($accounts);
    }

    public function searchAccountNumber(Request $request)
    {
        try {
            $validator = $this->accountValidation->checkGetListAccountIdValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $accountId = $this->accountService->searchAccountNumber($request->all());
            return response()->json($accountId);
        } catch (BusinessException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function create()
    {
        return view('index');
    }

    public function show(Account $account)
    {
        $account = $this->accountService->findById($account->id);
        return response()->json($account);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->accountValidation->checkAccountValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $account = $this->accountService->store($request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'account',
                    'user_id' => Auth::user()->id,
                    'row_id' => $account->id,
                    'action' => 'create new account',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_005'),
                    'data' => $account
                ],
                201
            );
        } catch (BusinessException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $this->accountValidation->checkUpdateAccountValidation(
                $request,
                $id
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $account = $this->accountService->update($id, $request);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'account',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update account',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_006'),
                    'data' => $request->all()
                ]
            );
        } catch (BusinessException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'account',
                'user_id' => Auth::user()->id,
                'row_id' => $id,
                'action' => 'delete account',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_007'),
            ]
        );
    }

    public function getListAccountBalances(Request $request)
    {
        $dataListAccount = $this->accountService->getListAccountBalances($request);
        $dataHistoryDetail = $this->accountService->getListAccountBalancesHistory($request);
        return response()->json(
            [
                'data_list_account' => $dataListAccount,
                'data_history_detail' => $dataHistoryDetail
            ]
        );
    }

    public function getAccountNumberByClient(Request $request)
    {
        try {
            $validator = $this->accountValidation->checkGetAccountNumberByClient(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $accountNumber = $this->accountService->getAccountNumberByClient($request);
            return response()->json($accountNumber);
        } catch (BusinessException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function checkUniqueAccountNumber(Request $request)
    {
        try {
            $validator = $this->accountValidation->checkUniqueAccountNumberValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $account = $this->accountService->checkUniqueAccountNumber($request);
            return $account;
        } catch (BusinessException $e) {
            throw $e;
        }
    }
}