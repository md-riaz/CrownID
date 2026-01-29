<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Realm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientManagementController extends Controller
{
    public function index()
    {
        $clients = Client::with('realm')->paginate(15);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $realms = Realm::all();
        return view('admin.clients.create', compact('realms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'realm_id' => 'required|exists:realms,id',
            'client_id' => 'required|string',
            'name' => 'required|string',
            'secret' => 'required|string',
            'redirect_uris' => 'required|string',
            'enabled' => 'boolean',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['redirect_uris'] = json_encode(array_map('trim', explode(',', $validated['redirect_uris'])));
        $validated['grant_types'] = json_encode(['authorization_code', 'refresh_token']);
        $validated['revoked'] = false;

        Client::create($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function edit(Client $client)
    {
        $realms = Realm::all();
        $client->redirect_uris = is_array($client->redirect_uris) 
            ? implode(', ', $client->redirect_uris)
            : implode(', ', json_decode($client->redirect_uris, true) ?? []);
        return view('admin.clients.edit', compact('client', 'realms'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'redirect_uris' => 'required|string',
            'enabled' => 'boolean',
        ]);

        $validated['redirect_uris'] = json_encode(array_map('trim', explode(',', $validated['redirect_uris'])));

        $client->update($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
