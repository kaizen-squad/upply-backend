<?php

namespace App\Exceptions;

use DomainException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class ApiException extends Exception
{
    public static function render(Throwable $e): array
    {
        return match(true){
            $e instanceof DomainException => self::domain($e),
            $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => self::forbidden($e),

            default => self::serverError($e)
        };
    }

    private static function forbidden(Throwable $e): array
    {
        return [
            'status' => 403,
            'success' => false,
            'message' => "You are not authorized to perform this action.",
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

    private static function serverError(Throwable $e): array
    {
        return [
            'status' => 500,
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : "An unexpected error occurred.",
            'data' => null
        ];
    }
}
