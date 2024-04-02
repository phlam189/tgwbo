<?php

namespace App\Repositories\Eloquent;

use App\Models\LogActionHistory;
use App\Repositories\Interfaces\LogActionHistoryRepositoryInterface;

class LogActionHistoryRepository extends BaseRepository implements LogActionHistoryRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return LogActionHistory::class;
    }
}
