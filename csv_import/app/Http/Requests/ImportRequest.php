<?php

namespace App\Http\Requests;

use App\Rules\CSVHeaderRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public $implicit = true;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'import_file' =>  [
                'required',
                'file',
                'max:10240',                 // KB (10 MB)
                'mimes:csv,txt',             // based on extension
                'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
                new CSVHeaderRule([
                    'full_name',
                    'email',
                    'phone',
                    'house_address',
                    'apartment',
                    'gas',
                    'electricity',
                    'heating',
                    'territory',
                    'water'
                ])
            ],
        ];
    }
}
