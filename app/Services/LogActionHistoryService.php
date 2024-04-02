<?php

namespace App\Services;

use App\Repositories\Interfaces\LogActionHistoryRepositoryInterface;

class LogActionHistoryService
{
    public LogActionHistoryRepositoryInterface $logActionHistoryRepository;

    public function __construct(LogActionHistoryRepositoryInterface $logActionHistoryRepository)
    {
        $this->logActionHistoryRepository = $logActionHistoryRepository;
    }

    public function store($data)
    {
        return $this->logActionHistoryRepository->create($data);
    }
}
