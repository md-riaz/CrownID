<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use App\Services\RealmExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RealmExportController extends Controller
{
    public function __construct(
        protected RealmExportService $exportService
    ) {}

    public function export(Request $request, string $realmName): JsonResponse
    {
        $realm = Realm::where('name', $realmName)->firstOrFail();
        
        $includeUsers = $request->boolean('includeUsers', false);
        
        $exportData = $this->exportService->exportRealm($realm, $includeUsers);
        
        $filename = $realmName . '-realm' . ($includeUsers ? '-with-users' : '') . '.json';
        
        return response()->json($exportData)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }
}
