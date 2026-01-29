<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Realm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(Request $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        
        $perPage = min((int) $request->get('max', 10), 100);
        $first = (int) $request->get('first', 0);
        
        $query = Client::where('realm_id', $realmModel->id);
        
        if ($request->has('clientId')) {
            $query->where('client_id', $request->get('clientId'));
        }
        
        $clients = $query->skip($first)->take($perPage)->get();
        
        return response()->json(ClientResource::collection($clients));
    }

    public function show(string $realm, string $id): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json(new ClientResource($client));
    }

    public function store(StoreClientRequest $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $validated = $request->validated();
        
        $isPublic = $validated['publicClient'] ?? false;
        $clientType = $isPublic ? 'public' : 'confidential';
        
        $secret = null;
        if (!$isPublic) {
            $secret = $validated['secret'] ?? Str::random(40);
        }
        
        $redirectUris = $validated['redirectUris'] ?? [];
        
        $client = Client::create([
            'id' => (string) Str::uuid(),
            'realm_id' => $realmModel->id,
            'client_id' => $validated['clientId'],
            'name' => $validated['name'] ?? $validated['clientId'],
            'secret' => $secret,
            'redirect_uris' => json_encode($redirectUris),
            'grant_types' => json_encode(['authorization_code', 'refresh_token']),
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'client_type' => $clientType,
            'enabled' => $validated['enabled'] ?? true,
        ]);

        return response()->json(new ClientResource($client), 201);
    }

    public function update(UpdateClientRequest $request, string $realm, string $id): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validated();
        
        $updateData = [];
        
        if (isset($validated['clientId'])) {
            $updateData['client_id'] = $validated['clientId'];
        }
        
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        
        if (isset($validated['enabled'])) {
            $updateData['enabled'] = $validated['enabled'];
        }
        
        if (isset($validated['publicClient'])) {
            $updateData['client_type'] = $validated['publicClient'] ? 'public' : 'confidential';
        }
        
        if (isset($validated['secret'])) {
            $updateData['secret'] = $validated['secret'];
        }
        
        if (isset($validated['redirectUris'])) {
            $updateData['redirect_uris'] = json_encode($validated['redirectUris']);
        }

        $client->update($updateData);
        $client->refresh();

        return response()->json(new ClientResource($client));
    }

    public function destroy(string $realm, string $id): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $client = Client::where('realm_id', $realmModel->id)
            ->where('id', $id)
            ->firstOrFail();

        $client->delete();

        return response()->json(null, 204);
    }
}
