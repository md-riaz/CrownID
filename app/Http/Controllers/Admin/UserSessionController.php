<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserSessionController extends Controller
{
    public function getSessions(string $realmName, int $userId): JsonResponse
    {
        $realm = Realm::where('name', $realmName)->firstOrFail();
        $user = User::where('realm_id', $realm->id)->findOrFail($userId);
        
        $sessions = \DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => date('Y-m-d H:i:s', $session->last_activity),
                ];
            });
        
        return response()->json($sessions);
    }

    public function deleteSessions(string $realmName, int $userId): JsonResponse
    {
        $realm = Realm::where('name', $realmName)->firstOrFail();
        $user = User::where('realm_id', $realm->id)->findOrFail($userId);
        
        \DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
        
        $user->tokens()->delete();
        
        return response()->json(['message' => 'All sessions deleted'], 200);
    }
}
