<?php

namespace OpenSoutheners\LaravelScim\Enums;

enum ScimAttributeReturned: string
{
    case Default = 'default';

    case Excluded = 'excluded';

    case Always = 'always';

    case Never = 'never';
}
