<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Validations\IntroducerInformationvalidation;
use App\Models\IntroducerInformation;
use App\Models\User;
use App\Services\IntroducerService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IntroducerInformationController extends Controller
{
    protected $introducerValidation;
    protected $introducerService;
    protected $logActionHistoryService;

    public function __construct(IntroducerInformationvalidation $introducerValidation, IntroducerService $introducerService, LogActionHistoryService $logActionHistoryService)
    {
        $this->introducerValidation = $introducerValidation;
        $this->introducerService = $introducerService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index()
    {
        $introducerInformation = $this->introducerService->getList();
        return response()->json($introducerInformation);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            $validator = $this->introducerValidation->checkIntroducerInformationValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $introducerInformation = $this->introducerService->store($request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'introducer_infomation',
                    'user_id' => Auth::user()->id,
                    'row_id' => $introducerInformation->id,
                    'action' => 'create new introducer infomation',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_017'),
                    'data' => $introducerInformation
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
            $validator = $this->introducerValidation->checkIntroducerInformationUpdateValidation(
                $id,
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $introducerInformation = $this->introducerService->update($id, $request);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'introducer_infomation',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update introducer infomation',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_018'),
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

    public function show(IntroducerInformation $introducer)
    {
        $introducer = $this->introducerService->showDetail($introducer->id);
        return response()->json($introducer);
    }

    public function destroy($id)
    {
        $introducerInformation = IntroducerInformation::findOrFail($id);
        $introducerInformation->delete();
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'introducer_infomation',
                'user_id' => Auth::user()->id,
                'row_id' => $id,
                'action' => 'delete introducer infomation',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_019'),
            ]
        );
    }

    public function checkUniqueEmail(Request $request)
    {
        try {
            $validator = $this->introducerValidation->checkUniqueEmailValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $introducer = $this->introducerService->checkUniqueEmail($request);
            return $introducer;
        } catch (BusinessException $e) {
            throw $e;
        }
    }

    public function showWithContractor($id)
    {
        $client = $this->introducerService->showWithContractor($id);
        return response()->json($client);
    }
}
