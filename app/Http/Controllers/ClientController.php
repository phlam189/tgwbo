<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Validations\ClientValidation;
use App\Models\Client;
use App\Models\User;
use App\Services\ClientService;
use App\Services\LogActionHistoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    protected $clientValidation;
    protected $clientService;
    protected $logActionHistoryService;

    public function __construct(ClientValidation $clientValidation, ClientService $clientService, LogActionHistoryService $logActionHistoryService)
    {
        $this->clientValidation = $clientValidation;
        $this->clientService = $clientService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index()
    {
        $clients = $this->clientService->getList();
        return response()->json($clients);
    }

    public function getClient(Request $request)
    {
        $clientId = $this->clientService->getClient($request->is_deleted);
        return response()->json($clientId);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->clientValidation->checkClientValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $client = $this->clientService->store($request->all());
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'client',
                    'user_id' => Auth::user()->id,
                    'row_id' => $client->id,
                    'action' => 'create new client',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_011'),
                    'data' => $client
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

    public function show(Client $client)
    {
        $clientContractDetail = $this->clientService->findById($client->id);
        return response()->json($clientContractDetail);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = $this->clientValidation->checkClientUpdateValidation(
                $id,
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $client = $this->clientService->update($id, $request);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'client',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update client',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_012'),
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

    public function destroy(Client $client)
    {
        $client->delete();
        $logActionHistory = $this->logActionHistoryService->store(
            [
                'table_name' => 'client',
                'user_id' => Auth::user()->id,
                'row_id' => $client->id,
                'action' => 'delete client',
            ]
        );
        return response()->json(
            [
                'message' =>  __('messages.EUA_013'),
            ]
        );
    }

    public function checkUniqueEmail(Request $request)
    {
        try {
            $validator = $this->clientValidation->checkUniqueEmailValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $contractor = $this->clientService->checkUniqueEmail($request);
            return $contractor;
        } catch (BusinessException $e) {
            throw $e;
        }
    }

    public function showWithContractor($id)
    {
        $client = $this->clientService->showWithContractor($id);
        return response()->json($client);
    }

    public function checkUniqueClientId(Request $request)
    {
        try {
            $validator = $this->clientValidation->checkUniqueClientIdValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $client = $this->clientService->checkUniqueClientId($request);
            return $client;
        } catch (BusinessException $e) {
            throw $e;
        }
    }
}
