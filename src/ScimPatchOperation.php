<?php

namespace OpenSoutheners\LaravelScim;

enum ScimPatchOperation: string
{
    case Add = 'add';

    case Replace = 'replace';

    case Remove = 'remove';
}
