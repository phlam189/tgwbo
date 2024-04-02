<?php

namespace App\Repositories\Interfaces;

interface InvoiceContructorRepositoryInterface extends RepositoryInterface
{
    public function getList($request);
    public function getListByContractorId($request, $date);
    public function findByNumber($request);
}
