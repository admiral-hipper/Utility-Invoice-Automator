<?php

namespace App\DTOs;

use App\Trait\StaticCreateSelf;
use App\Trait\ToArray;

class CustomerDTO
{
    use ToArray, StaticCreateSelf;

    public function __construct(
        readonly public string $full_name,
        readonly public string $phone,
        readonly public string $house_address,
        readonly public string $apartment
    ) {}
}
