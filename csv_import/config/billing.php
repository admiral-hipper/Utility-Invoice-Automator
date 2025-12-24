<?php

return [
    // Company
    'company_name' => env('BILLING_COMPANY_NAME', 'Utilities Billing SRL'),
    'company_id'   => env('BILLING_COMPANY_ID', 'RO12345678'), // CUI/VAT ID
    'address'      => env('BILLING_ADDRESS', 'Romania, Timisoara, ...'),
    'email'        => env('BILLING_EMAIL', 'billing@company.tld'),
    'phone'        => env('BILLING_PHONE', '+40...'),

    // Bank
    'bank_name' => env('BILLING_BANK_NAME', 'Banca ...'),
    'iban'      => env('BILLING_IBAN', 'RO00BANK0000000000000000'),
    'swift'     => env('BILLING_SWIFT', 'BANKROBU'),

    // Payment
    'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'RON'),
    'due_days'         => (int) env('BILLING_DUE_DAYS', 10),

    // paymentRef as: <prefix>:<invoice_no>
    'payment_ref_prefix' => env('BILLING_PAYMENT_REF_PREFIX', 'PAY'),
];
