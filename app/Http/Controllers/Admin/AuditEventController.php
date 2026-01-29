<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Realm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditEventController extends Controller
{
    public function index(Request $request, string $realmName): JsonResponse
    {
        $realm = Realm::where('name', $realmName)->firstOrFail();
        
        $query = AuditEvent::where('realm_id', $realm->id)
            ->with(['user:id,username,email']);
        
        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }
        
        $events = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 100));
        
        return response()->json([
            'events' => $events->items(),
            'total' => $events->total(),
            'per_page' => $events->perPage(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
        ]);
    }
}
