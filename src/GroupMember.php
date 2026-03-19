<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Contracts\Support\Arrayable;

readonly class GroupMember implements Arrayable
{
    public function __construct(
        public string $value,
        public ?string $display = null,
        public string $type = 'User',
        public ?string $ref = null,
    ) {
        //
    }

    public function toArray(): array
    {
        return array_filter([
            'value' => $this->value,
            'display' => $this->display,
            'type' => $this->type,
            '$ref' => $this->ref,
        ], fn ($v) => $v !== null);
    }
}
