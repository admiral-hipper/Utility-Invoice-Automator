<?php

namespace App;

enum ImportStatus: string
{
    case DRAFT = 'draft';
    case PAYED = 'payed';
    case ISSUED = 'issued';
    case CANCELED = 'canceled';
}
