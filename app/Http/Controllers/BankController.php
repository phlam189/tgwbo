<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Http\Validations\BankValidation;
use App\Models\User;
use App\Services\BankService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    protected $bankValidation;
    protected $bankService;
    protected $logActionHistoryService;

    public function __construct(BankValidation $bankValidation, BankService $bankService, LogActionHistoryService $logActionHistoryService)
    {
        $this->bankValidation = $bankValidation;
        $this->bankService = $bankService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index()
    {
        $banks = $this->bankService->getList();
        return response()->json($banks);
    }

    public function create()
    {
        return view('index');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->bankValidation->checkBankValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $bank = $this->bankService->store($request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'bank',
                    'user_id' => Auth::user()->id,
                    'row_id' => $bank->id,
                    'action' => 'create new bank',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_008'),
                    'data' => $bank
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

    public function show(Bank $bank)
    {
        return response()->json($bank);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $this->bankValidation->checkBankValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $bank = $this->bankService->update($id, $request);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'bank',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update bank',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_009'),
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
        $bank = Bank::findOrFail($id);
        $bank->delete();
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'bank',
                'user_id' => Auth::user()->id,
                'row_id' => $id,
                'action' => 'delete bank',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_010'),
            ]
        );
    }

    public function getListBankName()
    {
        $banks = $this->bankService->getListBankName();
        return response()->json($banks);
    }
}
