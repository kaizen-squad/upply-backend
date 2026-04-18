<?php

namespace App\Exceptions;

use DomainException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class ApiException extends Exception
{
    public static function render(Throwable $e): array
    {
        return match(true){
            $e instanceof DomainException => self::domain($e),
            $e instanceof AuthorizationException => self::forbidden($e)
        };
    }

    private static function forbidden(): array
    {
        return [
            'status' => 403,
            'success' => false,
            'message' => "You don't authorized to perform this action.",
            'data' => null
        ];
    }

    private static function domain(DomainException $e): array
    {
        return [
            'status' => 422,
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null
        ];
    }
}
