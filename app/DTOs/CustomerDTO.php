<?php

namespace App\DTOs;

use App\Traits\StaticCreateSelf;
use App\Traits\ToArray;

class CustomerDTO
{
    use StaticCreateSelf, ToArray;

    public function __construct(
        public readonly string $full_name,
        public readonly string $phone,
        public readonly string $house_address,
        public readonly string $apartment
    ) {}
}
