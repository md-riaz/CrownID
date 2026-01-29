<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\Realm;
use App\Services\RealmExportService;
use App\Services\RealmImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportExportController extends Controller
{
    protected $exportService;
    protected $importService;

    public function __construct(RealmExportService $exportService, RealmImportService $importService)
    {
        $this->exportService = $exportService;
        $this->importService = $importService;
    }

    public function index(Realm $realm)
    {
        return view('admin.import-export.index', compact('realm'));
    }

    public function export(Request $request, Realm $realm)
    {
        $includeUsers = $request->boolean('include_users', false);
        
        $data = $this->exportService->exportRealm($realm, $includeUsers);
        
        $filename = $realm->name . '-realm-export.json';
        
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    public function importForm()
    {
        return view('admin.import-export.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $json = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($json, true);

        if (!$data) {
            return back()->with('error', 'Invalid JSON file');
        }

        try {
            $realm = $this->importService->importRealm($data);

            return redirect()->route('admin.realms.index')
                ->with('success', 'Realm imported successfully: ' . $realm->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
