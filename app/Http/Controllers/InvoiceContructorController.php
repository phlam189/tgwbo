<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Validations\InvoiceContructorValidation;
use App\Models\InvoiceContructor;
use App\Services\InvoiceContructorService;
use App\Traits\ApiResponser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceContructorController extends Controller
{
    use ApiResponser;

    protected $invoiceContructorValidation;
    protected $invoiceContructorService;

    public function __construct(InvoiceContructorValidation $invoiceContructorValidation, InvoiceContructorService $invoiceContructorService)
    {
        $this->invoiceContructorValidation = $invoiceContructorValidation;
        $this->invoiceContructorService = $invoiceContructorService;
    }

    public function index(Request $request)
    {
        $invoiceContructorList = $this->invoiceContructorService->getListByContractorId($request);
        $clients = $this->invoiceContructorService->getList($request, $invoiceContructorList);
        return response()->json($clients);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->invoiceContructorValidation->checkInvoiceContructorValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $invoiceContructor = $this->invoiceContructorService->store($request->all());
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_026'),
                    'data' => $invoiceContructor
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

    public function show(InvoiceContructor $invoiceContructor)
    {
        return response()->json($invoiceContructor);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $this->invoiceContructorValidation->checkUpdateInvoiceContructorValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $this->invoiceContructorService->update($id, $request);
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_027'),
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

    public function destroy(InvoiceContructor $client)
    {
        $client->delete();
        return response()->json(
            [
                'message' =>  __('messages.EUA_028'),
            ]
        );
    }

    public function checkInvoiceNumber($idContractor, $number)
    {
        $result = $this->invoiceContructorService->checkInvoiceNumber($idContractor, $number);
        return $this->successResponse($result);
    }
}
