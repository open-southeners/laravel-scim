<?php

namespace OpenSoutheners\LaravelScim\Http\Resources;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use OpenSoutheners\LaravelScim\Exceptions\DuplicatedPostScimRecord;
use OpenSoutheners\LaravelScim\Support\SCIM;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * @property \Throwable $resource
 */
class JsonScimErrorResource extends JsonResource
{
    /**
     * Customize the outgoing response for the resource.
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->header('Content-Type', SCIM::contentTypeHeader());

        $response->setStatusCode(
            match (true) {
                $this->resource instanceof ValidationException => 400,
                $this->resource instanceof DuplicatedPostScimRecord => 409,
                $this->resource instanceof AuthenticationException => 401,
                $this->resource instanceof AuthorizationException => 403,
                $this->resource instanceof RecordsNotFoundException => 404,
                $this->resource instanceof HttpExceptionInterface => $this->resource->getStatusCode() ?? 500,
                default => 500,
            },
        );
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        [$scimType, $errorCode, $detail] = match (true) {
            $this->resource instanceof ValidationException => ['invalidSyntax', 0, ''],
            $this->resource instanceof DuplicatedPostScimRecord => [null, 0, 'Trying to create a duplicated resource.'],
            $this->resource instanceof AuthenticationException => [null, 5003, 'Not authenticated'],
            $this->resource instanceof RecordsNotFoundException => [null, null, 'Specified resource (e.g., User) or endpoint, does not exist.'],
            $this->resource instanceof AuthorizationException, $this->resource instanceof AccessDeniedHttpException => [null, 6008, 'Not found or authorized'],
            default => [null, 0, 'Unknown error, please try again later.'],
        };

        if ($this->resource instanceof ValidationException) {
            foreach ($this->resource->errors() as $field => $errors) {
                if (! empty($detail)) {
                    $detail .= "\n";
                }

                $detail .= "{$field}: " . implode(', ', $errors);
            }
        }

        return [
            'schemas' => 'urn:ietf:params:scim:api:messages:2.0:Error',
            $this->mergeWhen(!is_null($scimType), fn() => ['scimType' => $scimType]),
            'detail' => $detail,
            $this->mergeWhen(!is_null($errorCode), fn() => ['errorCode' => $errorCode]),
        ];
    }
}
