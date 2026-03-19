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
        public readonly ?ScimBadRequestErrorType $type = null,
        public readonly int $httpStatus = 400,
        string $detail = '',
        ?Throwable $previous = null,
    ) {
        if (! $detail) {
            $errorType = $type?->value ?? 'unknown';
            $detail = __("laravel-scim::errors.{$errorType}");
        }

        parent::__construct(message: $detail, previous: $previous);
    }

    /**
     * Render the exception as an HTTP response (RFC 7644 §3.12).
     */
    public function render(Request $request): JsonResponse
    {
        return new JsonResponse(array_filter([
            'schemas' => self::SCIM_SCHEMAS,
            'status' => (string) $this->httpStatus,
            'scimType' => $this->type?->value,
            'detail' => $this->getMessage(),
        ]), $this->httpStatus);
    }
}
