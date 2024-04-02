<?php

namespace App\Services;

use App\Repositories\Interfaces\ChargeHistoryRepositoryInterface;
use App\Repositories\Interfaces\ClientAggregationRepositoryInterface;
use Carbon\Carbon;

class ChargeHistoryService
{
    public ChargeHistoryRepositoryInterface $chargeHistoryRepository;
    public ClientAggregationRepositoryInterface $clientAggregationRepository;

    public function __construct(
        ChargeHistoryRepositoryInterface     $chargeHistoryRepository,
        ClientAggregationRepositoryInterface $clientAggregationRepository
    )
    {
        $this->chargeHistoryRepository = $chargeHistoryRepository;
        $this->clientAggregationRepository = $clientAggregationRepository;
    }

    public function store($data)
    {
        $this->clientAggregationRepository->createClientAggregationByAccountNumber($data);
        return $this->chargeHistoryRepository->create($data);
    }

    public function update($id, $request)
    {
        return $this->chargeHistoryRepository->update($id, $request->all());
    }

    public function find($id)
    {
        return $this->chargeHistoryRepository->find($id);
    }

    public function getList($request)
    {
        if (isset($request['from_date'])) {
            $fromDate = Carbon::parse($request['from_date'])->startOfMonth()->format('Y-m-d');
            $toDate = Carbon::parse($request['from_date'])->endOfMonth()->format('Y-m-d');
        } else {
            $fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $toDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
        return $this->chargeHistoryRepository->getList($request, $fromDate, $toDate);
    }

    public function getBorrowingByClient($clientId, $date, $type) {
        return $this->chargeHistoryRepository->getBorrowingByClient($clientId, $date, $type);
    }
    public function getRepaymentByClient($clientId, $date, $type) {
        return $this->chargeHistoryRepository->getRepaymentByClient($clientId, $date, $type);
    }
}
