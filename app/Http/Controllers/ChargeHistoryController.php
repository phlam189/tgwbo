<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Validations\ChargeHistoryValidation;
use App\Models\Account;
use App\Models\ChargeHistory;
use App\Services\AccountBalanceHistoryService;
use App\Services\ChargeHistoryService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChargeHistoryController extends Controller
{
    protected $chargeValidation;
    protected $chargeService;
    protected $logActionHistoryService;
    protected AccountBalanceHistoryService $accountBalanceHistoryService;

    public function __construct(
        ChargeHistoryValidation $chargeValidation,
        ChargeHistoryService $chargeService,
        LogActionHistoryService $logActionHistoryService,
        AccountBalanceHistoryService $accountBalanceHistoryService
    )
    {
        $this->chargeValidation = $chargeValidation;
        $this->chargeService = $chargeService;
        $this->logActionHistoryService = $logActionHistoryService;
        $this->accountBalanceHistoryService = $accountBalanceHistoryService;
    }

    public function index(Request $request)
    {

        $chargeHistory = $this->chargeService->getList($request);
        return response()->json($chargeHistory);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->chargeValidation->checkChargeHistoryValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $chargeHistory = $this->chargeService->store($request->all());

            $this->logActionHistoryService->store(
                [
                    'table_name' => 'charge history',
                    'user_id' => Auth::user()->id,
                    'row_id' => $chargeHistory->id,
                    'action' => 'create new charge history',
                ]
            );
            DB::commit();
            if($request->account_number) {
                $this->accountBalanceHistoryService->reCalculateAccountBalance($request->client_id, $request->account_number, $request->create_date);
                if ($chargeHistory->type == ChargeHistory::TRANSFER) {
                    $accountDeposit = Account::where('client_id', $request->client_id)->where('service_type', 1)->first();
                    $accountNumber = $accountDeposit ? $accountDeposit->account_number : 'not-found';
                    $this->accountBalanceHistoryService->reCalculateAccountBalance($request->client_id, $accountNumber, $request->create_date, 0,1);
                }
            }
            return response()->json(
                [
                    'message' =>  __('messages.EUA_011'),
                    'data' => $chargeHistory
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

    public function show(ChargeHistory $chargeHistory)
    {
        return response()->json($chargeHistory);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $this->chargeValidation->checkUpdateChargeHistoryValidation(
                $id,
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $chargeHistory = $this->chargeService->update($id, $request);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'charge history',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update charge history',
                ]
            );
            DB::commit();
            if ($chargeHistory) {
                $this->accountBalanceHistoryService->reCalculateAccountBalance($request->client_id, $request->account_number, $request->create_date);
                if ($request->type == ChargeHistory::TRANSFER) {
                    $accountDeposit = Account::where('client_id', $request->client_id)->where('service_type', 1)->first();
                    $accountNumber = $accountDeposit ? $accountDeposit->account_number : 'not-found';
                    $this->accountBalanceHistoryService->reCalculateAccountBalance($request->client_id, $accountNumber, $request->create_date);
                }
            }
            return response()->json(
                [
                    'message' => __('messages.EUA_012'),
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

    public function destroy(chargeHistory $chargeHistory)
    {
        $clientId = $chargeHistory->client_id;
        $accountNumber = $chargeHistory->account_number;
        $createDate = $chargeHistory->create_date;
        $typeCharge = $chargeHistory->type;
        $chargeHistory->delete();
        $this->accountBalanceHistoryService->reCalculateAccountBalance($clientId, $accountNumber, $createDate);
        if ($typeCharge == ChargeHistory::TRANSFER) {
            $accountDeposit = Account::where('client_id', $clientId)->where('service_type', 1)->first();
            $accountNumber = $accountDeposit ? $accountDeposit->account_number : 'not-found';
            $this->accountBalanceHistoryService->reCalculateAccountBalance($clientId, $accountNumber, $createDate);
        }
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'charge history',
                'user_id' => Auth::user()->id,
                'row_id' => $chargeHistory->id,
                'action' => 'delete charge history',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_013'),
            ]
        );
    }
}
