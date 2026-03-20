# Discovery Endpoints

SCIM defines three discovery endpoints that identity providers use to auto-configure their SCIM client. These endpoints are served automatically when you install the package.

## ServiceProviderConfig

```
GET /scim/v2/ServiceProviderConfig
```

Returns the capabilities of your SCIM server:

```json
{
    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:ServiceProviderConfig"],
    "patch": {"supported": true},
    "bulk": {"supported": false, "maxPayloadSize": 4194304, "maxOperations": 10},
    "filter": {"supported": true, "maxResults": 100},
    "changePassword": {"supported": false},
    "sort": {"supported": false},
    "etag": {"supported": false},
    "authenticationSchemes": [
        {
            "type": "oauthbearertoken",
            "name": "OAuth Bearer Token",
            "description": "Authentication scheme using the OAuth Bearer Token Standard",
            "specUri": "https://www.rfc-editor.org/info/rfc6750"
        }
    ]
}
```

The `authenticationSchemes` section reflects what you configured via `SCIM::authenticationSchemes()`.

## Schemas

### List all schemas

```
GET /scim/v2/Schemas
```

Returns attribute definitions for all registered schemas. Identity providers use this to understand which attributes your server supports.

### Get single schema

```
GET /scim/v2/Schemas/{schemaUrn}
```

Returns the definition for a specific schema URN.

## ResourceTypes

```
GET /scim/v2/ResourceTypes
```

Returns all available resource types with their endpoints:

```json
{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:ListResponse"],
    "totalResults": 2,
    "Resources": [
        {
            "schemas": ["urn:ietf:params:scim:schemas:core:2.0:ResourceType"],
            "id": "User",
            "name": "User",
            "endpoint": "/scim/v2/Users",
            "schema": "urn:ietf:params:scim:schemas:core:2.0:User"
        },
        {
            "schemas": ["urn:ietf:params:scim:schemas:core:2.0:ResourceType"],
            "id": "Group",
            "name": "Group",
            "endpoint": "/scim/v2/Groups",
            "schema": "urn:ietf:params:scim:schemas:core:2.0:Group"
        }
    ]
}
```

## Identity provider setup

When configuring SCIM in your identity provider, point it to your application's base URL:

| Provider | SCIM Base URL |
|---|---|
| Microsoft Entra ID | `https://yourapp.com/scim/v2` |
| Okta | `https://yourapp.com/scim/v2` |
| OneLogin | `https://yourapp.com/scim/v2` |
| JumpCloud | `https://yourapp.com/scim/v2` |

The identity provider will automatically fetch `/ServiceProviderConfig`, `/Schemas`, and `/ResourceTypes` to discover your server's capabilities.
