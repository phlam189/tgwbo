<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Validations\AccountBalanceValidation;
use App\Models\AccountBalanceHistory;
use App\Services\AccountBalanceHistoryService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AccountBalanceHistoryController extends Controller
{
    protected $accountBalanceHistoryService;
    protected $accountBalanceHistoryValidation;

    public function __construct(AccountBalanceHistoryService $accountBalanceHistoryService, AccountBalanceValidation $accountBalanceHistoryValidation)
    {
        $this->accountBalanceHistoryService = $accountBalanceHistoryService;
        $this->accountBalanceHistoryValidation = $accountBalanceHistoryValidation;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->accountBalanceHistoryValidation->checkAccountBalanceHistoryValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $accountBalanceHistory = AccountBalanceHistory::whereDate('date_history', $request->date_history)->where('account_number', $request->account_number)->first();
            if ($accountBalanceHistory) {
                $accountBalanceHistory = $this->accountBalanceHistoryService->update($accountBalanceHistory->id, $request);
            } else {
                $accountBalanceHistory = $this->accountBalanceHistoryService->store($request->all());
            }
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_005'),
                    'data' => $accountBalanceHistory
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
            $validator = $this->accountBalanceHistoryValidation->checkAccountBalanceHistoryValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $accountBalanceHistory = $this->accountBalanceHistoryService->update($id, $request);
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

    public function getAccountBalances(Request $request)
    {
        $accountBalanceHistory = $this->accountBalanceHistoryService->getList($request);
        return response()->json($accountBalanceHistory);
    }
}
