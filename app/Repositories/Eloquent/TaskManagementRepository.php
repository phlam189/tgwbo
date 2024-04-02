<?php

namespace App\Repositories\Eloquent;

use App\Models\TaskManagement;
use App\Repositories\Interfaces\TaskManagementRepositoryInterface;
use Illuminate\Support\Facades\Artisan;

class TaskManagementRepository extends BaseRepository implements TaskManagementRepositoryInterface
{
    /**
     * getModel
     *
     * @return string
     */
    public function getModel(): string
    {
        return TaskManagement::class;
    }

    public function updateTask($id)
    {
        $record = $this->model->findOrFail($id);
        $record->update([
            'status' => 1,
            'count' => 0,
        ]);
        Artisan::call('sync:data ' . $id);
        return $this->model->findOrFail($id);
    }

    public function getAll()
    {
        return $this->model->orderBy('status', 'desc')->orderBy('id', 'desc')->leftJoin('client', 'task_management.client_id', '=', 'client.client_id')
            ->select(
                'task_management.*',
                'client.represent_name as client_name',
            )->paginate(env('PAGINATE_DEFAULT', 30));
    }

    public function updateStatus($request)
    {
        $record = $this->model->findOrFail($request->id);
        $record->update([
            'status' => $request->status,
        ]);
        return $this->model->findOrFail($request->id);
    }
}
