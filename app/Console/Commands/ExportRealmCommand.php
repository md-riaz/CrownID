<?php

namespace App\Console\Commands;

use App\Models\Realm;
use App\Services\RealmExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportRealmCommand extends Command
{
    protected $signature = 'crownid:export {realm} {--file=} {--users}';

    protected $description = 'Export a realm to JSON format';

    public function handle(RealmExportService $exportService)
    {
        $realmName = $this->argument('realm');
        $includeUsers = $this->option('users');
        $file = $this->option('file');

        $realm = Realm::where('name', $realmName)->first();

        if (!$realm) {
            $this->error("Realm '{$realmName}' not found");
            return 1;
        }

        $this->info("Exporting realm: {$realmName}");
        
        $exportData = $exportService->exportRealm($realm, $includeUsers);
        
        if ($file) {
            $filePath = $file;
        } else {
            $filePath = storage_path('app/' . $realmName . '-realm' . ($includeUsers ? '-with-users' : '') . '.json');
        }

        File::put($filePath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Realm exported successfully to: {$filePath}");
        
        return 0;
    }
}
