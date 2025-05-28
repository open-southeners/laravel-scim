<?php

return [

    'invalidFilter' => 'The specified filter syntax was invalid or the specified attribute and filter comparison combination is not supported.',
    'tooMany'       => 'The specified filter yields many more results than the server is willing calculate or process.',
    'uniqueness'    => 'One or more of attribute values is already in use or is reserved.',
    'mutability'    => 'The attempted modification is not compatible with the target attributes mutability or current state.',
    'invalidSyntax' => 'The request body message structure was invalid or did not conform to the request schema.',
    'invalidPath'   => 'The path attribute was invalid or malformed.',
    'noTarget'      => 'The specified "path" did not yield an attribute or attribute value that could be operated on.',
    'invalidValue'  => 'A required value was missing, or the value specified was not compatible with the operation or attribute type.',
    'invalidVers'   => 'The specified SCIM protocol version is not supported.',
    'sensitive'     => 'The specified request cannot be completed due to passing of sensitive.',

    'unknown'       => 'Internal SCIM server error.',

];
