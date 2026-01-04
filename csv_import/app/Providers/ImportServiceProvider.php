<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Import\ImportManager;
use App\Services\Import\Importers\CSVImporter;
// use App\Services\Import\Importers\XlsxImporter;

class ImportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('import.importers', function () {
            return [
                $this->app->make(CSVImporter::class),
                // $this->app->make(XlsxImporter::class),
            ];
        });

        $this->app->singleton(ImportManager::class, function ($app) {
            return new ImportManager($app->make('import.importers'));
        });
    }
}
