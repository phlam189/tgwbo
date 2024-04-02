<?php

namespace App\Repositories\Interfaces;

interface TaskManagementRepositoryInterface extends RepositoryInterface
{
    public function getAll();
    public function updateTask($id);
    public function updateStatus($request);
}