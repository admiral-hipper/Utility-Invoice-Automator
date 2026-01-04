<?php

namespace App\Services\Import\Importers;

use Generator;
use App\DTOs\ImportRowDTO;
use App\Services\Import\Importer;
use League\Csv\Reader;

class CSVImporter extends Importer
{
    private Reader $csv;

    public function supports(string $filepath): bool
    {
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        return $ext === 'csv';
    }

    public function __construct() {}

    public function getRows(string $filepath): Generator
    {
        $this->csv = Reader::from($filepath, 'r')->setHeaderOffset(0);
        $this->validateHeader($this->csv->getHeader());
        foreach ($this->csv->getRecords() as $id => $record) {
            $this->validateRow(array_merge(['id' => $id], $record));
            yield ImportRowDTO::create($record);
        }
    }
}
