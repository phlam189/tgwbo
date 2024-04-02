<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface AccountRepositoryInterface extends RepositoryInterface
{
    public function getList();
    public function searchAccountNumber($request);
    public function getListAccountBalances(Request $request);
    public function getListAccountBalancesHistory(Request $request);
    public function getAccountNumberByClient($request);
    public function checkUniqueAccountNumber($request);
    public function findById($id);
}