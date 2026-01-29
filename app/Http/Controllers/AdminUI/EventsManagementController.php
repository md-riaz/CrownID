<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use App\Models\AuditEvent;
use Illuminate\Http\Request;

class EventsManagementController extends Controller
{
    public function index(Request $request, Realm $realm)
    {
        $query = AuditEvent::where('realm_id', $realm->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        // Filter by event type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $events = $query->paginate(50);

        $eventTypes = AuditEvent::distinct()->pluck('type');

        return view('admin.events.index', compact('realm', 'events', 'eventTypes'));
    }

    public function show(Realm $realm, AuditEvent $event)
    {
        $event->load('user');
        return view('admin.events.show', compact('realm', 'event'));
    }

    public function clear(Realm $realm)
    {
        AuditEvent::where('realm_id', $realm->id)->delete();

        return redirect()->route('admin.events.index', $realm)
            ->with('success', 'All events cleared successfully');
    }
}
