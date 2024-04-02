<?php

namespace App\Services;

use App\Repositories\Interfaces\LogTaskRepositoryInterface;

class LogTaskService
{
    public LogTaskRepositoryInterface $logTaskRepository;

    public function __construct(LogTaskRepositoryInterface $logTaskRepository)
    {
        $this->logTaskRepository = $logTaskRepository;
    }

    public function getList()
    {
        return $this->logTaskRepository->getList();
    }

}
