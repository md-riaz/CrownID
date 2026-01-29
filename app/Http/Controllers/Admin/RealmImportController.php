<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RealmImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class RealmImportController extends Controller
{
    public function __construct(
        protected RealmImportService $importService
    ) {}

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'realm' => 'required|array',
        ]);

        try {
            $realm = $this->importService->importRealm($validated['realm']);
            
            return response()->json([
                'message' => 'Realm imported successfully',
                'realm' => $realm->name,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Import failed',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function importDirectory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'directory' => 'required|string',
        ]);

        $directory = $validated['directory'];

        if (!File::isDirectory($directory)) {
            return response()->json([
                'error' => 'Invalid directory',
                'message' => 'The specified path is not a directory',
            ], 400);
        }

        $files = File::files($directory);
        $jsonFiles = array_filter($files, function ($file) {
            return $file->getExtension() === 'json' && $file->isFile();
        });

        if (empty($jsonFiles)) {
            return response()->json([
                'error' => 'No JSON files found',
                'message' => 'No .json files found in the specified directory',
            ], 400);
        }

        $imported = [];
        $failed = [];

        foreach ($jsonFiles as $file) {
            try {
                $content = File::get($file->getPathname());
                $realmData = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $failed[] = [
                        'file' => $file->getFilename(),
                        'error' => 'Invalid JSON: ' . json_last_error_msg(),
                    ];
                    continue;
                }

                $realm = $this->importService->importRealm($realmData);
                $imported[] = [
                    'file' => $file->getFilename(),
                    'realm' => $realm->name,
                ];
            } catch (\Exception $e) {
                $failed[] = [
                    'file' => $file->getFilename(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => sprintf(
                'Imported %d realm(s), %d failed',
                count($imported),
                count($failed)
            ),
            'imported' => $imported,
            'failed' => $failed,
        ], count($failed) > 0 && count($imported) === 0 ? 400 : 200);
    }
}
