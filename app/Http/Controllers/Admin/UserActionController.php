<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use App\Models\RequiredAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserActionController extends Controller
{
    public function getRequiredActions(string $realmName, int $userId): JsonResponse
    {
        $realm = Realm::where('name', $realmName)->firstOrFail();
        $user = User::where('realm_id', $realm->id)->findOrFail($userId);
        
        $actions = $user->requiredActions()->get()->map(function ($action) {
            return [
                'action' => $action->action,
                'required' => $action->required,
                'completed' => $action->isCompleted(),
                'completed_at' => $action->completed_at?->toIso8601String(),
            ];
        });
        
        return response()->json($actions);
    }

    public function addRequiredAction(Request $request, string $realmName, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:verify_email,update_password,configure_totp',
            'required' => 'boolean',
        ]);
        
        $realm = Realm::where('name', $realmName)->firstOrFail();
        $user = User::where('realm_id', $realm->id)->findOrFail($userId);
        
        $action = RequiredAction::updateOrCreate(
            [
                'user_id' => $user->id,
                'action' => $validated['action'],
            ],
            [
                'required' => $validated['required'] ?? true,
                'completed_at' => null,
            ]
        );
        
        return response()->json([
            'action' => $action->action,
            'required' => $action->required,
            'completed' => $action->isCompleted(),
        ], 201);
    }

    public function removeRequiredAction(string $realmName, int $userId, string $action): JsonResponse
    {
        $realm = Realm::where('name', $realmName)->firstOrFail();
        $user = User::where('realm_id', $realm->id)->findOrFail($userId);
        
        RequiredAction::where('user_id', $user->id)
            ->where('action', $action)
            ->delete();
        
        return response()->json(null, 204);
    }
}
