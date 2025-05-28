<?php

namespace OpenSoutheners\LaravelScim\Enums;

enum ScimBadRequestErrorType: string
{
    case InvalidFilter = 'invalidFilter';

    case TooMany = 'tooMany';

    case Uniqueness = 'uniqueness';

    case Mutability = 'mutability';

    case InvalidSyntax = 'invalidSyntax';

    case InvalidPath = 'invalidPath';

    case NoTarget = 'noTarget';

    case InvalidValue = 'invalidValue';

    case InvalidVersion = 'invalidVers';

    case Sensitive = 'sensitive';
}
