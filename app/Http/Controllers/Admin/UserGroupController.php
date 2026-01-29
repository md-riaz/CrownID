<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserGroupController extends Controller
{
    public function index(string $realm, string $userId): AnonymousResourceCollection
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        return GroupResource::collection($user->groups);
    }

    public function store(Request $request, string $realm, string $userId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $validated = $request->validate([
            'groupId' => 'required|exists:groups,id',
        ]);

        $group = Group::where('realm_id', $realmModel->id)
            ->where('id', $validated['groupId'])
            ->firstOrFail();

        $user->groups()->syncWithoutDetaching([$group->id]);

        return response()->json(null, 204);
    }

    public function destroy(string $realm, string $userId, string $groupId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $user = User::where('realm_id', $realmModel->id)
            ->where('id', $userId)
            ->firstOrFail();

        $user->groups()->detach($groupId);

        return response()->json(null, 204);
    }
}
