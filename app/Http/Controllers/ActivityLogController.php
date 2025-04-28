<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Http\Requests\ActivityLogRequest;
use App\DTOs\ActivityLogFilterDTO;

class ActivityLogController extends Controller
{
    protected ActivityLogService $service;

    public function __construct(ActivityLogService $service)
    {
        $this->service = $service;
    }

    public function index(ActivityLogRequest $request)
    {
        $filterDTO = ActivityLogFilterDTO::fromArray($request->validated());

        $logs = $this->service->getLogs($filterDTO)->appends($request->query());

        return view('logs.index', compact('logs'));
    }
}
