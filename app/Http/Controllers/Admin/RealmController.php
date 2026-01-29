<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRealmRequest;
use App\Http\Requests\Admin\UpdateRealmRequest;
use App\Http\Resources\RealmResource;
use App\Models\Realm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RealmController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $realms = Realm::all();
        return RealmResource::collection($realms);
    }

    public function show(string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        return response()->json(new RealmResource($realmModel));
    }

    public function store(StoreRealmRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $realm = Realm::create([
            'name' => $validated['realm'],
            'display_name' => $validated['displayName'] ?? $validated['realm'],
            'enabled' => $validated['enabled'] ?? true,
            'access_token_lifespan' => $validated['accessTokenLifespan'] ?? 300,
            'refresh_token_lifespan' => $validated['refreshTokenLifespan'] ?? 1800,
            'sso_session_idle_timeout' => $validated['ssoSessionIdleTimeout'] ?? 1800,
            'sso_session_max_lifespan' => $validated['ssoSessionMaxLifespan'] ?? 36000,
        ]);

        return response()->json(new RealmResource($realm), 201);
    }

    public function update(UpdateRealmRequest $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $validated = $request->validated();

        $updateData = [];
        
        if (isset($validated['realm'])) {
            $updateData['name'] = $validated['realm'];
        }
        if (isset($validated['displayName'])) {
            $updateData['display_name'] = $validated['displayName'];
        }
        if (isset($validated['enabled'])) {
            $updateData['enabled'] = $validated['enabled'];
        }
        if (isset($validated['accessTokenLifespan'])) {
            $updateData['access_token_lifespan'] = $validated['accessTokenLifespan'];
        }
        if (isset($validated['refreshTokenLifespan'])) {
            $updateData['refresh_token_lifespan'] = $validated['refreshTokenLifespan'];
        }
        if (isset($validated['ssoSessionIdleTimeout'])) {
            $updateData['sso_session_idle_timeout'] = $validated['ssoSessionIdleTimeout'];
        }
        if (isset($validated['ssoSessionMaxLifespan'])) {
            $updateData['sso_session_max_lifespan'] = $validated['ssoSessionMaxLifespan'];
        }

        $realmModel->update($updateData);

        return response()->json(new RealmResource($realmModel));
    }

    public function destroy(string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $realmModel->delete();

        return response()->json(null, 204);
    }
}
