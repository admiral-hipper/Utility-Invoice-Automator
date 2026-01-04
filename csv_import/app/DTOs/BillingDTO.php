<?php

namespace App\DTOs;

use App\Traits\StaticCreateSelf;

class BillingDTO
{
    use StaticCreateSelf;

    public function __construct(
        readonly public string $company_name,
        readonly public string $company_id,
        readonly public string $address,
        readonly public string $email,
        readonly public string $phone,
        readonly public string $iban,
        readonly public string $bank_name,
        readonly public string $swift
    ) {}
}
