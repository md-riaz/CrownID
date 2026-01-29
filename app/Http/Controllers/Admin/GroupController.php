<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\Realm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupController extends Controller
{
    public function index(string $realm): AnonymousResourceCollection
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $groups = Group::where('realm_id', $realmModel->id)->get();
        
        return GroupResource::collection($groups);
    }

    public function store(Request $request, string $realm): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent' => 'nullable|string',
        ]);

        $parentId = null;
        $path = '/' . $validated['name'];
        
        if (isset($validated['parent'])) {
            $parent = Group::where('realm_id', $realmModel->id)
                ->where('path', $validated['parent'])
                ->firstOrFail();
            $parentId = $parent->id;
            $path = $parent->path . '/' . $validated['name'];
        }

        $group = Group::create([
            'realm_id' => $realmModel->id,
            'parent_id' => $parentId,
            'name' => $validated['name'],
            'path' => $path,
        ]);

        return response()->json(new GroupResource($group), 201);
    }

    public function show(string $realm, string $groupId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $group = Group::where('realm_id', $realmModel->id)
            ->where('id', $groupId)
            ->firstOrFail();

        return response()->json(new GroupResource($group));
    }

    public function destroy(string $realm, string $groupId): JsonResponse
    {
        $realmModel = Realm::where('name', $realm)->firstOrFail();
        $group = Group::where('realm_id', $realmModel->id)
            ->where('id', $groupId)
            ->firstOrFail();

        $group->delete();

        return response()->json(null, 204);
    }
}
