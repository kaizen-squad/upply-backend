<?php

namespace App\Enums;

enum TransactionStatus : string
{
    case ESCROWLOCK = "BLOQUE";
    case RELEASED = "LIBERE";
    case FAILED = "ECHOUEE";
    case RELEASING = "EN_ATTENTE";

    
}
