<?php

namespace App\Services;

use App\Repositories\Interfaces\BankRepositoryInterface;

class BankService
{
    public BankRepositoryInterface $bankRepository;

    public function __construct(BankRepositoryInterface $bankRepository)
    {
        $this->bankRepository = $bankRepository;
    }

    public function store($data)
    {
        return $this->bankRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->bankRepository->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->bankRepository->find($id);
    }

    public function getList()
    {
        return $this->bankRepository->getList();
    }

    public function getListBankName()
    {
        return $this->bankRepository->getListBankName();
    }
}
