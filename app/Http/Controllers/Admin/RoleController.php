<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Realm;
use App\Models\Role;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    public function indexRealm(string $realm): AnonymousResourceCollection
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $roles = Role::where('realm_id', $realmModel->id)
            ->whereNull('client_id')
            ->get();
        
        return RoleResource::collection($roles);
    }

    public function storeRealm(Request $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'composite' => 'boolean',
        ]);

        $role = Role::create([
            'realm_id' => $realmModel->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'composite' => $validated['composite'] ?? false,
        ]);

        return response()->json(new RoleResource($role), 201);
    }

    public function showRealm(string $realm, string $roleName): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $role = Role::where('realm_id', $realmModel->id)
            ->whereNull('client_id')
            ->where('name', $roleName)
            ->firstOrFail();

        return response()->json(new RoleResource($role));
    }

    public function destroyRealm(string $realm, string $roleName): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $role = Role::where('realm_id', $realmModel->id)
            ->whereNull('client_id')
            ->where('name', $roleName)
            ->firstOrFail();

        $role->delete();

        return response()->json(null, 204);
    }

    public function indexClient(string $realm, string $clientId): AnonymousResourceCollection
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $clientId)
            ->firstOrFail();
        
        $roles = Role::where('client_id', $client->id)->get();
        
        return RoleResource::collection($roles);
    }

    public function storeClient(Request $request, string $realm, string $clientId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $clientId)
            ->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'composite' => 'boolean',
        ]);

        $role = Role::create([
            'realm_id' => $realmModel->id,
            'client_id' => $client->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'composite' => $validated['composite'] ?? false,
        ]);

        return response()->json(new RoleResource($role), 201);
    }

    public function showClient(string $realm, string $clientId, string $roleName): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $clientId)
            ->firstOrFail();
        
        $role = Role::where('client_id', $client->id)
            ->where('name', $roleName)
            ->firstOrFail();

        return response()->json(new RoleResource($role));
    }

    public function destroyClient(string $realm, string $clientId, string $roleName): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $clientId)
            ->firstOrFail();
        
        $role = Role::where('client_id', $client->id)
            ->where('name', $roleName)
            ->firstOrFail();

        $role->delete();

        return response()->json(null, 204);
    }
}
