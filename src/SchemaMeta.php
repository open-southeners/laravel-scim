<?php

namespace OpenSoutheners\LaravelScim;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;

readonly class SchemaMeta implements Arrayable
{
    public function __construct(
        public ?string $version = null,
        public ?string $url = null,
        public ?CarbonImmutable $created = null,
        public ?CarbonImmutable $lastModified = null,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'url' => $this->url,
            'created' => $this->created?->toIso8601ZuluString(),
            'lastModified' => $this->lastModified?->toIso8601ZuluString(),
        ];
    }
}
