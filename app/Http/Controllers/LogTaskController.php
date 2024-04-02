<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LogTask;
use App\Services\LogTaskService;
use App\Services\TaskManagementService;
use Illuminate\Http\Request;

class LogTaskController extends Controller
{
    protected $logTaskService;
    protected $taskManagementService;

    public function __construct(LogTaskService $logTaskService, TaskManagementService $taskManagementService)
    {
        $this->logTaskService = $logTaskService;
        $this->taskManagementService = $taskManagementService;
    }

    public function index()
    {
        $logTask = $this->logTaskService->getList();
        return response()->json($logTask);
    }

    public function update($id)
    {
        $logTask = $this->taskManagementService->updateStatus($id);
        return response()->json($logTask);
    }
}
