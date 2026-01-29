<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Realm;
use App\Models\Client;
use Illuminate\Http\Request;

class RolesManagementController extends Controller
{
    public function index(Realm $realm)
    {
        $realmRoles = Role::where('realm_id', $realm->id)
            ->whereNull('client_id')
            ->withCount('users')
            ->get();
        
        $clients = Client::where('realm_id', $realm->id)->get();
        
        return view('admin.roles.index', compact('realm', 'realmRoles', 'clients'));
    }

    public function create(Realm $realm)
    {
        $clients = Client::where('realm_id', $realm->id)->get();
        return view('admin.roles.create', compact('realm', 'clients'));
    }

    public function store(Request $request, Realm $realm)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $validated['realm_id'] = $realm->id;

        Role::create($validated);

        return redirect()->route('admin.roles.index', $realm)
            ->with('success', 'Role created successfully');
    }

    public function edit(Realm $realm, Role $role)
    {
        $role->load('compositeRoles', 'users');
        $availableRoles = Role::where('realm_id', $realm->id)
            ->where('id', '!=', $role->id)
            ->get();
        
        return view('admin.roles.edit', compact('realm', 'role', 'availableRoles'));
    }

    public function update(Request $request, Realm $realm, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $role->update($validated);

        return redirect()->route('admin.roles.edit', [$realm, $role])
            ->with('success', 'Role updated successfully');
    }

    public function destroy(Realm $realm, Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index', $realm)
            ->with('success', 'Role deleted successfully');
    }

    public function addComposite(Request $request, Realm $realm, Role $role)
    {
        $validated = $request->validate([
            'composite_role_id' => 'required|exists:roles,id',
        ]);

        $role->compositeRoles()->syncWithoutDetaching([$validated['composite_role_id']]);

        return redirect()->route('admin.roles.edit', [$realm, $role])
            ->with('success', 'Composite role added');
    }

    public function removeComposite(Realm $realm, Role $role, Role $compositeRole)
    {
        $role->compositeRoles()->detach($compositeRole->id);

        return redirect()->route('admin.roles.edit', [$realm, $role])
            ->with('success', 'Composite role removed');
    }

    public function clientRoles(Realm $realm, Client $client)
    {
        $clientRoles = Role::where('client_id', $client->id)
            ->withCount('users')
            ->get();
        
        return view('admin.roles.client-roles', compact('realm', 'client', 'clientRoles'));
    }
}
