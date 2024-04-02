<?php

namespace App\Repositories\Eloquent;

use App\Models\LogActionHistory;
use App\Models\LogTask;
use App\Repositories\Interfaces\LogTaskRepositoryInterface;

class LogTaskRepository extends BaseRepository implements LogTaskRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return LogTask::class;
    }

    public function getList()
    {
       return $this->model->orderBy('id', 'desc')->paginate(env('PAGINATE_DEFAULT', 30));
    }
}
