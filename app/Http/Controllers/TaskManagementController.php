<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Controllers\Controller;
use App\Models\IntroducerInformation;
use App\Models\TaskManagement;
use App\Services\LogActionHistoryService;
use App\Services\TaskManagementService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskManagementController extends Controller
{
    protected $taskManagementService;
    protected $logActionHistoryService;

    public function __construct(TaskManagementService $taskManagementService, LogActionHistoryService $logActionHistoryService)
    {
        $this->taskManagementService = $taskManagementService;
        $this->logActionHistoryService = $logActionHistoryService;
    }

    public function index()
    {
        $listTaskManagement = $this->taskManagementService->getAll();
        return response()->json($listTaskManagement);
    }

    public function show(TaskManagement $taskManagement)
    {
        return response()->json($taskManagement);
    }

    public function update($id)
    {
        DB::beginTransaction();
        try {
            $introducerInformation = $this->taskManagementService->updateTask($id);
            $logActionHistory = $this->logActionHistoryService->store(
                [
                    'table_name' => 'task_management',
                    'user_id' => Auth::user()->id,
                    'row_id' => $id,
                    'action' => 'update task management',
                ]
            );
            DB::commit();
            return response()->json(
                [
                    'message' =>  __('messages.EUA_029'),
                    'data' => $introducerInformation
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new BusinessException("EUA000", previous: $e);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $introducerInformation = $this->taskManagementService->updateStatus($request);
            return response()->json(
                [
                    'message' =>  __('messages.EUA_029'),
                    'data' => $introducerInformation
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new BusinessException("EUA000", previous: $e);
        }
    }
}
