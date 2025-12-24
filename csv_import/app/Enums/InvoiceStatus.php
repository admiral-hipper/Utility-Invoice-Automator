<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case PAYED = 'payed';
    case ISSUED = 'issued';
    case CANCELED = 'canceled';
}
