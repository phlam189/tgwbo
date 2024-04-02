<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface AccountBalanceHistoryRepositoryInterface extends RepositoryInterface
{
    public function getAccountBalanceByDate($clientId, $accountNumber, $toDate, $type);
    public function getList($request);
}
