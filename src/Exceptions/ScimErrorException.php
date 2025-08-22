<?php

namespace OpenSoutheners\LaravelScim\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenSoutheners\LaravelScim\Enums\ScimBadRequestErrorType;
use Throwable;

class ScimErrorException extends Exception
{
    public const SCIM_SCHEMAS = ['urn:ietf:params:scim:api:messages:2.0:Error'];

    public function __construct(
        public readonly array $errors,
        public readonly int $status = 400,
        public readonly ?ScimBadRequestErrorType $type = null,
        ?Throwable $previous = null,
    ) {
        $errorType = $type?->value ?? 'unknown';

        parent::__construct(message: __("laravel-scim::errors.{$errorType}"), previous: $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return new JsonResponse([
            'errors' => $this->errors,
            'schemas' => self::SCIM_SCHEMAS,
        ], $this->status);
    }
}
