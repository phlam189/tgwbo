<?php

namespace App\Repositories\Interfaces;

interface ContractorRepositoryInterface extends RepositoryInterface
{
   public function getList($request);
   public function getContractor();
   public function checkUniqueEmail($request);
   public function getListId($request);
   public function getAccountContractorById($id);
   public function getListContractor($request);
   public function findContractorIsHonsha($id = '');
   public function findContractor($id);
}
