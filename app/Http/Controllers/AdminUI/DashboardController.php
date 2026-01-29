<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use App\Models\AuditEvent;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'realms_count' => Realm::count(),
            'users_count' => User::count(),
            'clients_count' => Client::count(),
            'recent_logins' => AuditEvent::where('type', 'LOGIN_SUCCESS')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->with('user')
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
