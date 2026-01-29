<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        
        $perPage = min((int) $request->get('max', 10), 100);
        $first = (int) $request->get('first', 0);
        
        $query = User::where('realm_id', $realmModel->id);
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('username')) {
            $query->where('username', $request->get('username'));
        }
        
        if ($request->has('email')) {
            $query->where('email', $request->get('email'));
        }
        
        if ($request->has('firstName') || $request->has('lastName')) {
            $firstName = $request->get('firstName');
            $lastName = $request->get('lastName');
            
            $query->where(function ($q) use ($firstName, $lastName) {
                if ($firstName && $lastName) {
                    $q->where('name', 'like', "{$firstName}%{$lastName}%");
                } elseif ($firstName) {
                    $q->where('name', 'like', "{$firstName}%");
                } elseif ($lastName) {
                    $q->where('name', 'like', "%{$lastName}%");
                }
            });
        }
        
        $users = $query->skip($first)->take($perPage)->get();
        
        return response()->json(UserResource::collection($users));
    }

    public function show(string $realm, string $id): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json(new UserResource($user));
    }

    public function store(StoreUserRequest $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $validated = $request->validated();
        
        $firstName = $validated['firstName'] ?? '';
        $lastName = $validated['lastName'] ?? '';
        $name = trim("{$firstName} {$lastName}");
        
        $password = null;
        if (isset($validated['credentials']) && !empty($validated['credentials'])) {
            foreach ($validated['credentials'] as $credential) {
                if ($credential['type'] === 'password') {
                    $password = Hash::make($credential['value']);
                    break;
                }
            }
        }
        
        if (!$password) {
            $password = Hash::make(bin2hex(random_bytes(16)));
        }
        
        $user = User::create([
            'realm_id' => $realmModel->id,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'name' => $name ?: $validated['username'],
            'password' => $password,
            'attributes' => $validated['attributes'] ?? [],
            'email_verified_at' => ($validated['emailVerified'] ?? false) ? now() : null,
        ]);

        return response()->json(new UserResource($user), 201);
    }

    public function update(UpdateUserRequest $request, string $realm, string $id): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validated();
        
        $updateData = [];
        
        if (isset($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }
        
        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        
        if (isset($validated['firstName']) || isset($validated['lastName'])) {
            $currentNames = $this->parseUserName($user->name);
            $firstName = $validated['firstName'] ?? $currentNames['firstName'];
            $lastName = $validated['lastName'] ?? $currentNames['lastName'];
            $updateData['name'] = trim("{$firstName} {$lastName}");
        }
        
        if (isset($validated['attributes'])) {
            $updateData['attributes'] = $validated['attributes'];
        }
        
        if (isset($validated['emailVerified'])) {
            $updateData['email_verified_at'] = $validated['emailVerified'] ? now() : null;
        }

        $user->update($updateData);

        return response()->json(new UserResource($user));
    }

    public function destroy(string $realm, string $id): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $id)
            ->firstOrFail();

        $user->delete();

        return response()->json(null, 204);
    }

    private function parseUserName(string $name): array
    {
        $parts = explode(' ', $name, 2);
        return [
            'firstName' => $parts[0] ?? '',
            'lastName' => $parts[1] ?? '',
        ];
    }
}
