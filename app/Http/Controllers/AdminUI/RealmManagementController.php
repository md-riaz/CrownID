<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use Illuminate\Http\Request;

class RealmManagementController extends Controller
{
    public function index()
    {
        $realms = Realm::paginate(15);
        return view('admin.realms.index', compact('realms'));
    }

    public function create()
    {
        return view('admin.realms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:realms,name',
            'display_name' => 'required|string',
            'enabled' => 'boolean',
            'access_token_lifespan' => 'integer|min:60',
            'refresh_token_lifespan' => 'integer|min:300',
        ]);

        Realm::create($validated);

        return redirect()->route('admin.realms.index')
            ->with('success', 'Realm created successfully.');
    }

    public function edit(Realm $realm)
    {
        return view('admin.realms.edit', compact('realm'));
    }

    public function update(Request $request, Realm $realm)
    {
        $validated = $request->validate([
            'display_name' => 'required|string',
            'enabled' => 'boolean',
            'access_token_lifespan' => 'integer|min:60',
            'refresh_token_lifespan' => 'integer|min:300',
        ]);

        $realm->update($validated);

        return redirect()->route('admin.realms.index')
            ->with('success', 'Realm updated successfully.');
    }

    public function destroy(Realm $realm)
    {
        if ($realm->name === 'master') {
            return back()->with('error', 'Cannot delete master realm.');
        }

        $realm->delete();

        return redirect()->route('admin.realms.index')
            ->with('success', 'Realm deleted successfully.');
    }
}
