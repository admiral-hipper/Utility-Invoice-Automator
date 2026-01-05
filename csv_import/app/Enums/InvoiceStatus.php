<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case PAID = 'paid';
    case ISSUED = 'issued';
    case CANCELED = 'canceled';
}
