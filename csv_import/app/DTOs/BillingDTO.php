<?php

namespace App\DTOs;

use App\Traits\StaticCreateSelf;

class BillingDTO
{
    use StaticCreateSelf;

    public function __construct(
        public readonly string $company_name,
        public readonly string $company_id,
        public readonly string $address,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $iban,
        public readonly string $bank_name,
        public readonly string $swift
    ) {}
}
