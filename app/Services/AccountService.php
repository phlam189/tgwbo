<?php

namespace App\Services;

use App\Repositories\Interfaces\AccountRepositoryInterface;
use Illuminate\Http\Request;

class AccountService
{
    public AccountRepositoryInterface $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function store($data)
    {
        return $this->accountRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->accountRepository->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->accountRepository->find($id);
    }

    public function getList()
    {
        return $this->accountRepository->getList();
    }

    public function searchAccountNumber($request)
    {
        return $this->accountRepository->searchAccountNumber($request);
    }

    public function getListAccountBalances(Request $request)
    {
        return $this->accountRepository->getListAccountBalances($request);
    }

    public function getListAccountBalancesHistory(Request $request)
    {
        return $this->accountRepository->getListAccountBalancesHistory($request);
    }

    public function getAccountNumberByClient($request)
    {
        return $this->accountRepository->getAccountNumberByClient($request);
    }

    public function checkUniqueAccountNumber($request)
    {
        return $this->accountRepository->checkUniqueAccountNumber($request);
    }

    public function findById($id)
    {
        return $this->accountRepository->findById($id);
    }
}
