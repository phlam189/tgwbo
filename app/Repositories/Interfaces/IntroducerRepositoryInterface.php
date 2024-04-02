<?php

namespace App\Repositories\Interfaces;

interface IntroducerRepositoryInterface extends RepositoryInterface
{
    public function getList();
    public function checkUniqueEmail($request);
    public function showWithContractor($id);
    public function showDetail($id);
}
