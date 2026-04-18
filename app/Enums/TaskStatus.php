<?php

namespace App\Enums;

enum TaskStatus: string
{
    case OPENED = "OUVERTE";
    case PENDING = "EN_COURS";
    case DELIVERED = "LIVREE";
    case VALIDATED = "VALIDEE";

    public function isTerminal(): bool
    {
        return match($this){
            self::VALIDATED => true,
            self::OPENED, self::PENDING, self::DELIVERED => false
        };
    }
}
