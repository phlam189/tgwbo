<?php

namespace App\Services;

use App\Repositories\Interfaces\ContractorRepositoryInterface;

class ContractorService
{
    public ContractorRepositoryInterface $contractorRepository;

    public function __construct(ContractorRepositoryInterface $contractorRepository)
    {
        $this->contractorRepository = $contractorRepository;
    }

    public function store($data)
    {
        return $this->contractorRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->contractorRepository->update($id, $request);
    }

    public function find($id)
    {
        return $this->contractorRepository->find($id);
    }

    public function getList($request)
    {
        return $this->contractorRepository->getList($request);
    }

    public function getContractor()
    {
        return $this->contractorRepository->getContractor();
    }
    public function checkUniqueEmail($request)
    {
        return $this->contractorRepository->checkUniqueEmail($request);
    }

    public function getListId($request)
    {
        return $this->contractorRepository->getListId($request);
    }

    public function getAccountContractorById($id)
    {
        return $this->contractorRepository->getAccountContractorById($id);
    }

    public function getListContractor($request)
    {
        return $this->contractorRepository->getListContractor($request);
    }
    public function findContractorIsHonsha($id)
    {
        return $this->contractorRepository->findContractorIsHonsha($id);
    }

    public function findContractor($id)
    {
        return $this->contractorRepository->findContractor($id);
    }
}
