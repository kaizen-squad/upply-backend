<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case ESCROW_LOCK = 'escrow_lock';
    case RELEASING   = 'releasing';
    case RELEASED    = 'released';
    case FAILED      = 'failed';
}
