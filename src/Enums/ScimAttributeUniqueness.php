<?php

namespace OpenSoutheners\LaravelScim\Enums;

enum ScimAttributeUniqueness: string
{
    case None = 'none';

    case Server = 'server';

    case Global = 'global';
}
