<?php

namespace App\Services;

use App\Repositories\Interfaces\TaskManagementRepositoryInterface;

class TaskManagementService
{
    public TaskManagementRepositoryInterface $taskManagementRepository;

    public function __construct(TaskManagementRepositoryInterface $taskManagementRepository)
    {
        $this->taskManagementRepository = $taskManagementRepository;
    }

    public function updateTask($id)
    {
        return $this->taskManagementRepository->updateTask($id);
    }

    public function getAll()
    {
        return $this->taskManagementRepository->getAll();
    }

    public function updateStatus($request){
        return $this->taskManagementRepository->updateStatus($request);
    }

}