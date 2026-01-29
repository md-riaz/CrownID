<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Group;
use App\Models\RequiredAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserDetailsController extends Controller
{
    public function show(User $user)
    {
        return redirect()->route('admin.user-details.info', $user);
    }

    public function info(User $user)
    {
        $user->load('realm');
        return view('admin.user-details.info', compact('user'));
    }

    public function credentials(User $user)
    {
        $user->load('realm');
        return view('admin.user-details.credentials', compact('user'));
    }

    public function roleMappings(User $user)
    {
        $user->load(['realm', 'roles', 'groups.roles']);
        $realmRoles = Role::where('realm_id', $user->realm_id)
            ->whereNull('client_id')
            ->get();
        
        return view('admin.user-details.role-mappings', compact('user', 'realmRoles'));
    }

    public function groups(User $user)
    {
        $user->load(['realm', 'groups']);
        $availableGroups = Group::where('realm_id', $user->realm_id)->get();
        
        return view('admin.user-details.groups', compact('user', 'availableGroups'));
    }

    public function sessions(User $user)
    {
        $user->load('realm');
        // TODO: Get actual sessions for this user
        $sessions = [];
        return view('admin.user-details.sessions', compact('user', 'sessions'));
    }

    public function requiredActions(User $user)
    {
        $user->load('realm');
        $requiredActions = RequiredAction::where('user_id', $user->id)->get();
        $availableActions = ['VERIFY_EMAIL', 'UPDATE_PASSWORD', 'CONFIGURE_TOTP'];
        
        return view('admin.user-details.required-actions', compact('user', 'requiredActions', 'availableActions'));
    }

    public function updateInfo(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'enabled' => 'boolean',
            'email_verified' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('admin.user-details.info', $user)
            ->with('success', 'User information updated successfully');
    }

    public function setPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:6',
            'temporary' => 'boolean',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($validated['temporary'] ?? false) {
            RequiredAction::firstOrCreate([
                'user_id' => $user->id,
                'action' => 'UPDATE_PASSWORD',
                'required' => true,
            ]);
        }

        return redirect()->route('admin.user-details.credentials', $user)
            ->with('success', 'Password set successfully');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->roles()->syncWithoutDetaching([$validated['role_id']]);

        return redirect()->route('admin.user-details.role-mappings', $user)
            ->with('success', 'Role assigned successfully');
    }

    public function removeRole(User $user, Role $role)
    {
        $user->roles()->detach($role->id);

        return redirect()->route('admin.user-details.role-mappings', $user)
            ->with('success', 'Role removed successfully');
    }

    public function joinGroup(Request $request, User $user)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $user->groups()->syncWithoutDetaching([$validated['group_id']]);

        return redirect()->route('admin.user-details.groups', $user)
            ->with('success', 'Joined group successfully');
    }

    public function leaveGroup(User $user, Group $group)
    {
        $user->groups()->detach($group->id);

        return redirect()->route('admin.user-details.groups', $user)
            ->with('success', 'Left group successfully');
    }
}
