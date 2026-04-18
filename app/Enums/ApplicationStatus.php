<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = "EN_ATTENTE";
    case ACCEPTED = "ACCEPTEE";
    case REJECTED = "REJETEE";

    public function canTransitateTo(ApplicationStatus $targetStatus): bool
    {
        return match($this){
            self::REJECTED, self::ACCEPTED => false,
            self::PENDING => in_array($targetStatus, [self::ACCEPTED, self::REJECTED])
        };
    }
}
