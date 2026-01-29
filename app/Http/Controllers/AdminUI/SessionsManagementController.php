<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionsManagementController extends Controller
{
    public function index(Realm $realm)
    {
        // Get active sessions from database
        // This is a simplified version - in production, you'd query actual session storage
        $sessions = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.realm_id', $realm->id)
            ->select('sessions.*', 'users.username', 'users.email')
            ->orderBy('sessions.last_activity', 'desc')
            ->paginate(20);
        
        return view('admin.sessions.index', compact('realm', 'sessions'));
    }

    public function show(Realm $realm, $sessionId)
    {
        $session = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('sessions.id', $sessionId)
            ->select('sessions.*', 'users.username', 'users.email', 'users.name')
            ->first();
        
        return view('admin.sessions.show', compact('realm', 'session'));
    }

    public function destroy(Realm $realm, $sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();

        return redirect()->route('admin.sessions.index', $realm)
            ->with('success', 'Session terminated successfully');
    }

    public function destroyAll(Realm $realm)
    {
        DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.realm_id', $realm->id)
            ->delete();

        return redirect()->route('admin.sessions.index', $realm)
            ->with('success', 'All sessions terminated successfully');
    }
}
