<?php

namespace App\DTOs;

use App\Traits\StaticCreateSelf;
use App\Traits\ToArray;

class ImportRowDTO
{
    use ToArray, StaticCreateSelf;

    public function __construct(
        readonly public int $id,
        readonly public string $full_name,
        readonly public string $phone,
        readonly public string $email,
        readonly public string $house_address,
        readonly public string $apartment,
        readonly public float $gas,
        readonly public float $electricity,
        readonly public float $heating,
        readonly public float $territory,
        readonly public float $water,
        readonly string $currency,
    ) {}
}
