<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureSupervisorAccess($request);

        return response()->json(
            AuditLog::query()
                ->with('user:id,name,email')
                ->latest('logged_at')
                ->limit(100)
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'string'],
            'details' => ['required', 'string'],
        ]);

        $log = AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $validated['action'],
            'details' => $validated['details'],
            'logged_at' => now(),
        ]);

        return response()->json($log, 201);
    }

    private function ensureSupervisorAccess(Request $request): void
    {
        abort_unless(
            in_array(optional($request->user()->role)->name, ['Admin', 'Supervisor'], true),
            403,
            'Supervisor or admin access required.'
        );
    }
}
