# Filtering

SCIM clients can filter list results using the `filter` query parameter. The package parses SCIM filter syntax and translates it into Eloquent query conditions.

## Basic usage

```
GET /scim/v2/Users?filter=userName eq "john@example.com"
```

No code changes needed. Filtering works automatically for any registered schema.

## Supported operators

| Operator | Description | Example |
|---|---|---|
| `eq` | Equal | `userName eq "john@example.com"` |
| `ne` | Not equal | `active ne "false"` |
| `co` | Contains | `name co "john"` |
| `sw` | Starts with | `userName sw "john"` |
| `pr` | Present (not null) | `externalId pr` |
| `gt` | Greater than | `meta.created gt "2024-01-01"` |
| `ge` | Greater than or equal | `meta.created ge "2024-01-01"` |
| `lt` | Less than | `meta.created lt "2024-12-31"` |
| `le` | Less than or equal | `meta.created le "2024-12-31"` |

## Logical operators

Combine conditions with `and`, `or`, and `not`:

```
GET /scim/v2/Users?filter=userName eq "john@example.com" and active eq "true"
```

```
GET /scim/v2/Users?filter=name co "john" or name co "jane"
```

## Attribute mapping

Filter attribute names are automatically mapped to database columns using the `modelAttribute` defined in your `ScimSchemaAttribute`. For example:

```php
#[ScimSchemaAttribute(modelAttribute: 'email')]
string $userName,
```

A filter on `userName` will query the `email` column in the database.

## Pagination

SCIM uses 1-based pagination with `startIndex` and `count` parameters:

```
GET /scim/v2/Users?startIndex=1&count=25
```

| Parameter | Default | Description |
|---|---|---|
| `startIndex` | `1` | 1-based index of the first result |
| `count` | `10` | Number of results per page |

The response includes pagination metadata:

```json
{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:ListResponse"],
    "totalResults": 42,
    "itemsPerPage": 25,
    "startIndex": 1,
    "Resources": [...]
}
```

## Max results

The `filter.maxResults` config limits how many results can be returned per page:

```php
// config/scim.php
'filter' => [
    'maxResults' => 100,
],
```

If a client requests more than this via `count`, the limit is capped.
