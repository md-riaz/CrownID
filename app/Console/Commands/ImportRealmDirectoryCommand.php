<?php

namespace App\Console\Commands;

use App\Services\RealmImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportRealmDirectoryCommand extends Command
{
    protected $signature = 'crownid:import-directory {directory}';

    protected $description = 'Import realms from all JSON files in a directory';

    public function handle(RealmImportService $importService)
    {
        $directory = $this->argument('directory');

        if (!File::isDirectory($directory)) {
            $this->error("Directory not found: {$directory}");
            return 1;
        }

        $files = File::files($directory);
        $jsonFiles = array_filter($files, function ($file) {
            return $file->getExtension() === 'json' && $file->isFile();
        });

        if (empty($jsonFiles)) {
            $this->warn("No JSON files found in: {$directory}");
            return 0;
        }

        $this->info("Found " . count($jsonFiles) . " JSON file(s) in directory");

        $imported = 0;
        $failed = 0;

        foreach ($jsonFiles as $file) {
            $filename = $file->getFilename();
            $this->line("Processing: {$filename}");

            try {
                $content = File::get($file->getPathname());
                $realmData = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error("  ✗ Invalid JSON: " . json_last_error_msg());
                    $failed++;
                    continue;
                }

                $realm = $importService->importRealm($realmData);
                $this->info("  ✓ Imported realm: {$realm->name}");
                $imported++;
            } catch (\Exception $e) {
                $this->error("  ✗ Import failed: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Import complete: {$imported} succeeded, {$failed} failed");

        return $failed > 0 ? 1 : 0;
    }
}
