<?php

namespace App\Models;

use App\Policies\ImportPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UsePolicy(ImportPolicy::class)]
class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'file_path',
        'errors',
        'status',
        'total_rows'
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
