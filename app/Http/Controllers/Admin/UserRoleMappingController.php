<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Realm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoleMappingController extends Controller
{
    public function index(string $realm, string $userId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $realmRoles = $user->directRoles()
            ->whereNull('client_id')
            ->get();
        
        $clientRoles = [];
        $clientRoleModels = $user->directRoles()
            ->whereNotNull('client_id')
            ->with('client')
            ->get();
        
        foreach ($clientRoleModels as $role) {
            $clientId = $role->client->id;
            if (!isset($clientRoles[$clientId])) {
                $clientRoles[$clientId] = [
                    'id' => (string) $clientId,
                    'client' => $role->client->name,
                    'mappings' => []
                ];
            }
            $clientRoles[$clientId]['mappings'][] = new RoleResource($role);
        }

        return response()->json([
            'realmMappings' => RoleResource::collection($realmRoles),
            'clientMappings' => array_values($clientRoles),
        ]);
    }

    public function addRealmRoles(Request $request, string $realm, string $userId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            '*.id' => 'required|exists:crownid_roles,id',
            '*.name' => 'required|string',
        ]);

        $roleIds = array_column($validated, 'id');
        $roles = Role::whereIn('id', $roleIds)
            ->where('realm_id', $realmModel->id)
            ->whereNull('client_id')
            ->get();

        $user->directRoles()->syncWithoutDetaching($roles->pluck('id')->toArray());

        return response()->json(null, 204);
    }

    public function deleteRealmRoles(Request $request, string $realm, string $userId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            '*.id' => 'required|exists:crownid_roles,id',
            '*.name' => 'required|string',
        ]);

        $roleIds = array_column($validated, 'id');
        
        $user->directRoles()->detach($roleIds);

        return response()->json(null, 204);
    }

    public function addClientRoles(Request $request, string $realm, string $userId, string $clientId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            '*.id' => 'required|exists:crownid_roles,id',
            '*.name' => 'required|string',
        ]);

        $roleIds = array_column($validated, 'id');
        $roles = Role::whereIn('id', $roleIds)
            ->where('client_id', $clientId)
            ->get();

        $user->directRoles()->syncWithoutDetaching($roles->pluck('id')->toArray());

        return response()->json(null, 204);
    }

    public function deleteClientRoles(Request $request, string $realm, string $userId, string $clientId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            '*.id' => 'required|exists:crownid_roles,id',
            '*.name' => 'required|string',
        ]);

        $roleIds = array_column($validated, 'id');
        
        $user->directRoles()->detach($roleIds);

        return response()->json(null, 204);
    }
}
