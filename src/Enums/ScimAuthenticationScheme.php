<?php

namespace OpenSoutheners\LaravelScim\Enums;

enum ScimAuthenticationScheme: string
{
    case OAuthBearerToken = 'oauthbearertoken';

    case HttpBasic = 'httpbasic';

    public function toArray(): array
    {
        return match ($this) {
            self::OAuthBearerToken => [
                'name' => 'OAuth Bearer Token',
                'description' => 'Authentication scheme using the OAuth Bearer Token Standard',
                'specUri' => 'http://www.rfc-editor.org/info/rfc6750',
                'type' => 'oauthbearertoken',
            ],
            self::HttpBasic => [
                'name' => 'HTTP Basic',
                'description' => 'Authentication scheme using the HTTP Basic Authentication Standard',
                'specUri' => 'http://www.rfc-editor.org/info/rfc7617',
                'type' => 'httpbasic',
            ],
        };
    }
}
