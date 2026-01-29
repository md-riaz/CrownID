<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientDetailsController extends Controller
{
    public function show(Client $client)
    {
        return redirect()->route('admin.client-details.settings', $client);
    }

    public function settings(Client $client)
    {
        $client->load('realm');
        return view('admin.client-details.settings', compact('client'));
    }

    public function credentials(Client $client)
    {
        $client->load('realm');
        return view('admin.client-details.credentials', compact('client'));
    }

    public function roles(Client $client)
    {
        $client->load('realm');
        $clientRoles = Role::where('client_id', $client->id)->get();
        return view('admin.client-details.roles', compact('client', 'clientRoles'));
    }

    public function sessions(Client $client)
    {
        $client->load('realm');
        // TODO: Get actual sessions for this client
        $sessions = [];
        return view('admin.client-details.sessions', compact('client', 'sessions'));
    }

    public function updateSettings(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'enabled' => 'boolean',
            'redirect_uris' => 'required|string',
        ]);

        $validated['redirect_uris'] = json_encode(array_filter(array_map('trim', explode("\n", $validated['redirect_uris']))));

        $client->update($validated);

        return redirect()->route('admin.client-details.settings', $client)
            ->with('success', 'Client settings updated successfully');
    }

    public function regenerateSecret(Client $client)
    {
        $client->update([
            'secret' => Str::random(32),
        ]);

        return redirect()->route('admin.client-details.credentials', $client)
            ->with('success', 'Client secret regenerated successfully');
    }
}
