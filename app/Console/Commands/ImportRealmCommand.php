<?php

namespace App\Console\Commands;

use App\Services\RealmImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportRealmCommand extends Command
{
    protected $signature = 'crownid:import {file}';

    protected $description = 'Import a realm from JSON file';

    public function handle(RealmImportService $importService)
    {
        $file = $this->argument('file');

        if (!File::exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Importing realm from: {$file}");

        try {
            $content = File::get($file);
            $realmData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON: ' . json_last_error_msg());
                return 1;
            }

            $realm = $importService->importRealm($realmData);

            $this->info("Realm '{$realm->name}' imported successfully");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return 1;
        }
    }
}
