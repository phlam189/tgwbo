<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Validations\ContractorValidation;
use App\Models\Contractor;
use App\Models\User;
use App\Services\ContractorService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractorController extends Controller
{
    protected $contractorValidation;
    protected $contractorService;
    protected $logActionHistoryService;

    public function __construct(ContractorValidation $contractorValidation, ContractorService $contractorService, LogActionHistoryService $logActionHistoryService)
    {
        $this->contractorValidation = $contractorValidation;
        $this->contractorService = $contractorService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index(Request $request)
    {
        $contractors = $this->contractorService->getList($request);
        return response()->json($contractors);
    }

    public function getContractor(Request $request)
    {
        $contractorsId = $this->contractorService->getContractor($request);
        return response()->json($contractorsId);
    }
    public function listContractor()
    {
        $contractors = $this->contractorService->getContractor();
        return response()->json($contractors);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $request->is_honsha
                ? $this->contractorValidation->checkContractorValidation($request)
                : $this->contractorValidation->checkAccountContractorValidation($request);

            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $contractor = $this->contractorService->store($request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'account',
                    'user_id' => Auth::user()->id,
                    'row_id' => $contractor->id,
                    'action' => 'create new contractor',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_014'),
                    'data' => $contractor
                ],
                201
            );
        } catch (BusinessException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function show($id)
    {
        $contractorInformation = $this->contractorService->getAccountContractorById($id);
        return response()->json($contractorInformation);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $request->is_honsha
                ? $this->contractorValidation->checkContractorUpdateValidation($id, $request)
                : $this->contractorValidation->checkAccountContractorUpdateValidation($id, $request);

            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $contractor = $this->contractorService->update($id, $request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'contractor',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update contractor',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_015'),
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
        $contractor = Contractor::findOrFail($id);
        $request = [
            'email' => $contractor->id . '_' . $contractor->email,
        ];
        $this->contractorService->update($id, $request);
        $contractor->delete();
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'contractor',
                'user_id' => Auth::user()->id,
                'row_id' => $id,
                'action' => 'delete contractor',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_016'),
            ]
        );
    }

    public function checkUniqueEmail(Request $request)
    {
        try {
            $validator = $this->contractorValidation->checkUniqueEmailValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $contractor = $this->contractorService->checkUniqueEmail($request);
            return $contractor;
        } catch (BusinessException $e) {
            throw $e;
        }
    }

    public function getListId(Request $request)
    {
        $contractor = $this->contractorService->getListId($request);
        return $contractor;
    }
}
