<?php

namespace App\Services\Import;

use App\DTOs\ImportRowDTO;
use Generator;

interface UtilitiesImportInterface
{
    /**
     * @return Generator<ImportRowDTO>
     */
    public function getRows(string $filepath): Generator;

    /**
     * Checks if importer can be used
     */
    public function supports(string $filepath): bool;
}
