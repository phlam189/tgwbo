<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Http\Validations\ClientContractDetailValidation;
use App\Models\Client;
use App\Models\ClientContractDetail;
use App\Services\ClientContractService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientContractDetailController extends Controller
{
    protected $clientContractDetail;
    protected $clientDetailService;

    public function __construct(ClientContractDetailValidation $clientContractDetail, ClientContractService $clientDetailService)
    {
        $this->clientContractDetail = $clientContractDetail;
        $this->clientDetailService = $clientDetailService;
    }

    public function index(Request $request)
    {
        if ($request->client_id) {
            $client = Client::where('client_id', $request->client_id)->first();
            if($client) {
                if($client->contract_method == Client::FLAT) {
                    $clientContractDetail = ClientContractDetail::where('client_id', $request->client_id)
                        ->where('max_amount', 0)
                        ->paginate(env('PAGINATE_DEFAULT', 30));
                } else {
                    $clientContractDetail = ClientContractDetail::where('client_id', $request->client_id)->paginate(env('PAGINATE_DEFAULT', 30));
                }
            }
            else {
                $clientContractDetail = null;
            }
        } else {
            $clientContractDetail = ClientContractDetail::paginate(env('PAGINATE_DEFAULT', 30));
        }
        return response()->json($clientContractDetail);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $this->clientContractDetail->checkClientDetailValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $clientContractDetail = $this->clientDetailService->store($request->all());
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_023'),
                    'data' => $clientContractDetail
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
            $validator = $this->clientContractDetail->checkClientDetailValidation(
                $request
            );
            if ($validator->fails()) {
                throw new BusinessException($validator->errors()->first());
            }
            $clientContractDetail = $this->clientDetailService->update($id, $request);
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_024'),
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

    public function show(ClientContractDetail $clientDetail)
    {
            return response()->json($clientDetail);
    }

    public function destroy($id)
    {
        $clientContractDetail = ClientContractDetail::findOrFail($id);
        $clientContractDetail->delete();

        return response()->json(
            [
                'message' =>  __('messages.EUA_025'),
            ]
        );
    }

    public function checkContractDetailExist(Request $request) {
        $contractDetail = $this->clientDetailService->checkContractDetailExit($request);
        return response()->json(
            [
                'data' =>  $contractDetail,
            ]
        );
    }
}
