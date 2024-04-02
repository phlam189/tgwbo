<?php

namespace App\Repositories\Interfaces;

interface ClientRepositoryInterface extends RepositoryInterface
{
   public function getList();
   public function getClient();
   public function checkUniqueEmail($request);
   public function showWithContractor($id);
   public function findClient($id);
   public function checkUniqueClientId($request);
   public function findById($id);
}