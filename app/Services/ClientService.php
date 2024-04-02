<?php

namespace App\Services;

use App\Repositories\Interfaces\ClientContractDetailRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;

class ClientService
{
    public ClientRepositoryInterface $clientRepository;

    public ClientContractDetailRepositoryInterface $contractDetailRepository;

    public function __construct(ClientRepositoryInterface $clientRepository, ClientContractDetailRepositoryInterface $contractDetailRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->contractDetailRepository = $contractDetailRepository;
    }

    public function store($data)
    {
        return $this->clientRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->clientRepository->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->clientRepository->findClient($id);
    }

    public function getList()
    {
        return $this->clientRepository->getList();
    }

    public function getClient($isDeleted)
    {
        return $this->clientRepository->getClient($isDeleted);
    }

    public function checkUniqueEmail($request)
    {
        return $this->clientRepository->checkUniqueEmail($request);
    }

    public function showWithContractor($id)
    {
        return $this->clientRepository->showWithContractor($id);
    }

    public function checkUniqueClientId($request)
    {
        return $this->clientRepository->checkUniqueClientId($request);
    }

    public function findById($id)
    {
        $client = $this->clientRepository->findById($id);
        if ($client) {
            $client->client_detail = $this->contractDetailRepository->findBy(['client_id' => $client->client_id]);
        }
        return $client;
    }
}
