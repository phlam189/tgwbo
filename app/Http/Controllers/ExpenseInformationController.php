<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Validations\ExpenseValidation;
use App\Models\ExpenseInformation;
use App\Models\User;
use App\Services\ExpenseService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseInformationController extends Controller
{
    protected $expenseValidation;
    protected $expenseService;
    protected $logActionHistoryService;

    public function __construct(ExpenseValidation $expenseValidation, ExpenseService $expenseService, LogActionHistoryService $logActionHistoryService)
    {
        $this->expenseValidation = $expenseValidation;
        $this->expenseService = $expenseService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index()
    {
        $expenseInformation = $this->expenseService->getList();
        return response()->json($expenseInformation);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->expenseValidation->checkExpenseValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $expenseInformation = $this->expenseService->store($request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'introducer_infomation',
                    'user_id' => Auth::user()->id,
                    'row_id' => $expenseInformation->id,
                    'action' => 'create new expense infomation',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_020'),
                    'data' => $expenseInformation
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

    public function show(Request $request, $id)
    {
        $expenseInformation = $this->expenseService->getExpenseById($id);
        return response()->json($expenseInformation);
    }
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $this->expenseValidation->checkExpenseValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $expenseInformation = $this->expenseService->update($id, $request);
            $expenseNew = $this->expenseService->find($id);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'introducer_infomation',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update expense infomation',
                ]
            );
            $expenseNew = $this->expenseService->find($id);
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_021'),
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
        $expenseInformation = ExpenseInformation::findOrFail($id);
        $expenseInformation->delete();
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'introducer_infomation',
                'user_id' => Auth::user()->id,
                'row_id' => $expenseInformation->id,
                'action' => 'delete expense infomation',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_022'),
            ]
        );
    }
}
