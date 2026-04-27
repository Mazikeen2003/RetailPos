<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $logs = AuditLog::when($q, function ($query, $q) {
            $query->where('action', 'like', "%{$q}%")->orWhere('user', 'like', "%{$q}%")->orWhere('details', 'like', "%{$q}%");
        })->orderBy('created_at', 'desc')->get();

        return response()->json($logs);
    }
}
