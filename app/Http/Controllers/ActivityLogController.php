<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'ILIKE', '%' . $request->user . '%');
            });
        }

        if ($request->filled('action')) {
            $query->where('action', 'ILIKE', '%' . $request->action . '%');
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', 'ILIKE', '%' . $request->entity_type . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'ILIKE', '%' . $request->description . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(15)->appends($request->query());

        return view('logs.index', compact('logs'));
    }

}
