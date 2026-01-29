<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Realm;
use App\Models\Role;
use Illuminate\Http\Request;

class GroupsManagementController extends Controller
{
    public function index(Realm $realm)
    {
        $groups = Group::where('realm_id', $realm->id)
            ->whereNull('parent_id')
            ->with('children')
            ->get();
        
        return view('admin.groups.index', compact('realm', 'groups'));
    }

    public function create(Realm $realm)
    {
        $parentGroups = Group::where('realm_id', $realm->id)->get();
        return view('admin.groups.create', compact('realm', 'parentGroups'));
    }

    public function store(Request $request, Realm $realm)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'parent_id' => 'nullable|exists:groups,id',
        ]);

        $validated['realm_id'] = $realm->id;
        
        if ($validated['parent_id']) {
            $parent = Group::find($validated['parent_id']);
            $validated['path'] = $parent->path . '/' . $validated['name'];
        } else {
            $validated['path'] = '/' . $validated['name'];
        }

        Group::create($validated);

        return redirect()->route('admin.groups.index', $realm)
            ->with('success', 'Group created successfully');
    }

    public function edit(Realm $realm, Group $group)
    {
        $group->load('members', 'roles');
        $availableRoles = Role::where('realm_id', $realm->id)->whereNull('client_id')->get();
        
        return view('admin.groups.edit', compact('realm', 'group', 'availableRoles'));
    }

    public function update(Request $request, Realm $realm, Group $group)
    {
        $validated = $request->validate([
            'name' => 'required|string',
        ]);

        $group->update($validated);

        return redirect()->route('admin.groups.edit', [$realm, $group])
            ->with('success', 'Group updated successfully');
    }

    public function destroy(Realm $realm, Group $group)
    {
        $group->delete();

        return redirect()->route('admin.groups.index', $realm)
            ->with('success', 'Group deleted successfully');
    }

    public function assignRole(Request $request, Realm $realm, Group $group)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $group->roles()->syncWithoutDetaching([$validated['role_id']]);

        return redirect()->route('admin.groups.edit', [$realm, $group])
            ->with('success', 'Role assigned to group');
    }

    public function removeRole(Realm $realm, Group $group, Role $role)
    {
        $group->roles()->detach($role->id);

        return redirect()->route('admin.groups.edit', [$realm, $group])
            ->with('success', 'Role removed from group');
    }
}
