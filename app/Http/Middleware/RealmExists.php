<?php

namespace App\Http\Middleware;

use App\Models\Realm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RealmExists
{
    public function handle(Request $request, Closure $next): Response
    {
        $realmName = $request->route('realm');
        
        $realm = Realm::where('name', $realmName)
            ->where('enabled', true)
            ->first();
            
        if (!$realm) {
            abort(404, 'Realm not found');
        }
        
        $request->merge(['realm_model' => $realm]);
        
        return $next($request);
    }
}
