<?php

namespace OpenSoutheners\LaravelScim\Enums;

enum ScimAttributeMutability: string
{
    case ReadWrite = 'readWrite';

    case ReadOnly = 'readOnly';

    case WriteOnly = 'writeOnly';

    case Immutable = 'immutable';
}
