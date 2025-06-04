<?php

namespace OpenSoutheners\LaravelScim;

use Illuminate\Contracts\Support\Arrayable;

readonly class UserEmail implements Arrayable
{
    public function __construct(
        public string $value,
        public string $type,
        public bool $primary,
    ) {
        //
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'type' => $this->type,
            'primary' => $this->primary,
        ];
    }
}
