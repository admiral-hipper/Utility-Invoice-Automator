<?php

namespace App\Enums;

enum ImportStatus: string
{
    case QUEUED = 'queued';
    case FAILED = 'failed';
    case PROCESSED = 'processed';
    case ARCHIDED = 'archived';
}
