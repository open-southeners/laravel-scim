<?php

namespace OpenSoutheners\LaravelScim;

enum Features
{
    case Patch;

    case Bulk;

    case ChangePassword;

    case Filter;

    case Sort;

    case ETag;
}
