<?php

namespace App\DTOs;

use App\Traits\StaticCreateSelf;
use App\Traits\ToArray;

class ImportRowDTO
{
    use StaticCreateSelf, ToArray;

    public function __construct(
        public readonly string $full_name,
        public readonly string $phone,
        public readonly string $email,
        public readonly string $house_address,
        public readonly string $apartment,
        public readonly float $gas,
        public readonly float $electricity,
        public readonly float $heating,
        public readonly float $territory,
        public readonly float $water,
        public readonly string $currency,
    ) {}
}
