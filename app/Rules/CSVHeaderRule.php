<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CSVHeaderRule implements ValidationRule
{
    public function __construct(private array $requiredColumns, private string $delimiter = ',') {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_object($value) || ! method_exists($value, 'getRealPath')) {
            $fail('Invalid uploaded file.');

            return;
        }

        $h = @fopen($value->getRealPath(), 'r');
        if (! $h) {
            $fail('Cannot read uploaded file.');

            return;
        }

        $header = fgetcsv($h, 0, $this->delimiter) ?: [];
        fclose($h);

        $missing = array_diff($this->requiredColumns, $header);
        if ($missing) {
            $fail('Missing required CSV columns: '.implode(', ', $missing));
        }
    }
}
