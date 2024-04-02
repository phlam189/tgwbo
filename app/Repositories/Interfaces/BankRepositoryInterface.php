<?php

namespace App\Repositories\Interfaces;

interface BankRepositoryInterface extends RepositoryInterface
{
   public function getList();
   public function getListBankName();
}
