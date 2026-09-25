<?php

namespace App\Exceptions;

use DomainException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiException extends Exception
{
    public static function render(Throwable $e): array
    {
        return match (true) {
            $e instanceof DomainException => self::domain($e),
            $e instanceof ValidationException => self::validation($e),
            $e instanceof AuthenticationException => self::unauthenticated(),
            $e instanceof AuthorizationException, $e instanceof AccessDeniedHttpException => self::forbidden($e),
            $e instanceof NotFoundHttpException => self::notFound($e),
            $e instanceof HttpExceptionInterface => self::httpError($e),

            default => self::serverError()
        };
    }

    private static function forbidden(Throwable $e): array
    {
        $message = 'You are not authorized to perform this action.';
        if ($e instanceof AuthorizationException) {
            $message = $e->response()?->message() ?: $e->getMessage() ?: $message;
        } elseif ($e instanceof AccessDeniedHttpException) {
            $message = $e->getMessage() ?: $message;
        }

        return [
            'status' => 403,
            'success' => false,
            'code' => 'FORBIDDEN',
            'message' => $message,
            'data' => null,
        ];
    }

    private static function unauthenticated(): array
    {
        return [
            'status' => 401,
            'success' => false,
            'code' => 'UNAUTHENTICATED',
            'message' => 'Authentication is required.',
            'data' => null,
        ];
    }

    private static function notFound(NotFoundHttpException $e): array
    {
        return [
            'status' => 404,
            'success' => false,
            'code' => 'NOT_FOUND',
            'message' => $e->getMessage(),
            'data' => null,
        ];
    }

    private static function validation(ValidationException $e): array
    {
        return [
            'status' => 422,
            'success' => false,
            'code' => 'VALIDATION_ERROR',
            'message' => 'The given data was invalid.',
            'data' => $e->errors(),
        ];
    }

    private static function httpError(HttpExceptionInterface $e): array
    {
        $status = $e->getStatusCode();
        $message = $status >= 500
            ? 'An unexpected error occurred.'
            : ($e->getMessage() ?: 'The request could not be processed.');

        return [
            'status' => $status,
            'success' => false,
            'code' => 'HTTP_ERROR',
            'message' => $message,
            'data' => null,
        ];
    }

    private static function domain(DomainException $e): array
    {
        return [
            'status' => 422,
            'success' => false,
            'code' => 'DOMAIN_ERROR',
            'message' => $e->getMessage(),
            'data' => null,
        ];
    }

    private static function serverError(): array
    {
        return [
            'status' => 500,
            'success' => false,
            'code' => 'INTERNAL_SERVER_ERROR',
            'message' => 'An unexpected error occurred.',
            'data' => null,
        ];
    }

    public static function headers(Throwable $e): array
    {
        return $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];
    }
}
