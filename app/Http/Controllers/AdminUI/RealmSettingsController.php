<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use Illuminate\Http\Request;

class RealmSettingsController extends Controller
{
    public function show(Realm $realm)
    {
        return view('admin.realm-settings.index', compact('realm'));
    }

    public function general(Realm $realm)
    {
        return view('admin.realm-settings.general', compact('realm'));
    }

    public function login(Realm $realm)
    {
        return view('admin.realm-settings.login', compact('realm'));
    }

    public function tokens(Realm $realm)
    {
        return view('admin.realm-settings.tokens', compact('realm'));
    }

    public function security(Realm $realm)
    {
        return view('admin.realm-settings.security', compact('realm'));
    }

    public function updateGeneral(Request $request, Realm $realm)
    {
        $validated = $request->validate([
            'display_name' => 'required|string',
            'enabled' => 'boolean',
        ]);

        $realm->update($validated);

        return redirect()->route('admin.realm-settings.general', $realm)
            ->with('success', 'Realm settings updated successfully');
    }

    public function updateTokens(Request $request, Realm $realm)
    {
        $validated = $request->validate([
            'access_token_lifespan' => 'required|integer|min:60',
            'refresh_token_lifespan' => 'required|integer|min:300',
            'sso_session_idle_timeout' => 'nullable|integer|min:300',
            'sso_session_max_lifespan' => 'nullable|integer|min:300',
        ]);

        $realm->update($validated);

        return redirect()->route('admin.realm-settings.tokens', $realm)
            ->with('success', 'Token settings updated successfully');
    }

    public function updateSecurity(Request $request, Realm $realm)
    {
        $validated = $request->validate([
            'brute_force_protected' => 'boolean',
            'max_login_attempts' => 'nullable|integer|min:1',
            'lockout_duration_minutes' => 'nullable|integer|min:1',
        ]);

        $realm->update($validated);

        return redirect()->route('admin.realm-settings.security', $realm)
            ->with('success', 'Security settings updated successfully');
    }
}
