<?php

namespace App\Services\Import;

use RuntimeException;

class ImportManager
{
    /** @param UtilitiesImportInterface[] $importers */
    public function __construct(private iterable $importers) {}

    public function importerFor(string $filepath): UtilitiesImportInterface
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports($filepath)) {
                return $importer;
            }
        }

        throw new RuntimeException("No importer supports file: {$filepath}");
    }
}
