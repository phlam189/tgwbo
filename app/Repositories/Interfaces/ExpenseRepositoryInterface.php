<?php

namespace App\Repositories\Interfaces;

interface ExpenseRepositoryInterface extends RepositoryInterface
{
    public function getList();
    public function getExpenseById($id);
}
