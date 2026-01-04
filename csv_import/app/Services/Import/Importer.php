<?php

namespace App\Services\Import;

use App\Exceptions\ImportException;;

abstract class Importer implements UtilitiesImportInterface
{
    public const REQUIRED_HEADERS = [
        'full_name',
        'email',
        'phone',
        'house_address',
        'apartment',
        'gas',
        'electricity',
        'heating',
        'territory',
        'water',
    ];

    /**
     * Checks if row has valid value
     * @throws ImportException
     */
    protected function validateRow(array $row): void
    {
        foreach ($row as $column => $value) {
            if (empty($value)) {
                throw new ImportException("Value of $column is empty! (ID:{" . $row['id'] . ")");
            }
        }
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ImportException("Value of email is invalid! (ID:" . $row['id'] . ")");
        }
    }

    /**
     * Checks if file has correct headers
     * @throws ImportException
     */
    protected function validateHeader(array $header): void
    {
        $missing = array_diff(self::REQUIRED_HEADERS, $header);
        if (count($missing)) {
            throw new ImportException('Columns (' . implode(',', $missing) . ') are missing or not valid');
        }
    }
}
