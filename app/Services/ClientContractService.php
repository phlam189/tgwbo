<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientContractDetail;
use App\Repositories\Interfaces\ClientContractDetailRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use App\Enums\MessageCode;

class ClientContractService
{
    public ClientContractDetailRepositoryInterface $clientContractDetailRepositoryInterface;
    public ClientRepositoryInterface $clientRepositoryInterface;

    public function __construct(ClientContractDetailRepositoryInterface $clientContractDetailRepositoryInterface, ClientRepositoryInterface $clientRepositoryInterface)
    {
        $this->clientContractDetailRepositoryInterface = $clientContractDetailRepositoryInterface;
        $this->clientRepositoryInterface = $clientRepositoryInterface;
    }

    public function store($data)
    {
        return $this->clientContractDetailRepositoryInterface->create($data);
    }

    public function update($id, $request)
    {
        return $this->clientContractDetailRepositoryInterface->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->clientContractDetailRepositoryInterface->find($id);
    }

    public function checkContractDetailExit($param)
    {
        if($param->client_id) {
            $client = Client::where('client_id', $param->client_id)->first();
            if($client->contract_method == Client::FLAT) {
                $clientContractDetail = ClientContractDetail::where('client_id', $param->client_id)
                    ->where('service_type', $param->service_type)->first();
                if($param->id) {
                    $clientContractDetail = ClientContractDetail::where('client_id', $param->client_id)
                        ->where('id', '!=', $param->id)
                        ->where('service_type', $param->service_type)->first();
                }
                $status = $clientContractDetail ? true : false;
                return [
                    'status' => $status,
                    'message_code' => $status ? MessageCode::ADM_005_1 : null
                ];
            }
            if($param->max_amount == 0) {
                $clientContractDetail = ClientContractDetail::where('client_id', $param->client_id)
                  ->where('service_type', $param->service_type)
                  ->where('max_amount', 0)->first();
                if($param->id) {
                    $clientContractDetail = ClientContractDetail::where('client_id', $param->client_id)
                        ->where('id', '!=', $param->id)
                        ->where('max_amount', 0)
                        ->where('service_type', $param->service_type)->first();
                }
                $status = $clientContractDetail ? true : false;  
                return [
                    'status' => $status,
                    'message_code' => $status ? MessageCode::ADM_005_2 : null
                ];
            } else {
                $clientContractDetail = ClientContractDetail::where('client_id', $param->client_id)
                  ->where('service_type', $param->service_type)
                  ->where('max_amount', 0)->first();
                if($param->id) {
                  $clientContractDetail = ClientContractDetail::where('client_id', $param->client_id)
                      ->where('id', '!=', $param->id)
                      ->where('max_amount', 0)
                      ->where('service_type', $param->service_type)->first();
                }
                $status = $clientContractDetail ? false : true;
                return [
                    'status' => $status,
                    'message_code' => $status ? MessageCode::ADM_005_3 : null
                ];
            }
        }
        return;
    }
}