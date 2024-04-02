<?php

namespace App\Services;

use App\Repositories\Interfaces\ExpenseRepositoryInterface;

class ExpenseService
{
    public ExpenseRepositoryInterface $expenseRepository;

    public function __construct(ExpenseRepositoryInterface $expenseRepository)
    {
        $this->expenseRepository = $expenseRepository;
    }

    public function store($data)
    {
        return $this->expenseRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->expenseRepository->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->expenseRepository->find($id);
    }

    public function getList(){
        return $this->expenseRepository->getList();
    }

    public function getExpenseById($id){
        return $this->expenseRepository->getExpenseById($id);
    }
}
