<?php

namespace OpenSoutheners\LaravelScim\Enums;

enum ScimPatchOp: string
{
    case Add = 'add';

    case Remove = 'remove';

    case Replace = 'replace';
}
