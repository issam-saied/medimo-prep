<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ActivityLog::class);

        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(15);

        return ActivityLogResource::collection($logs);
    }
}
