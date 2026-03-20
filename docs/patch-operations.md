# PATCH Operations

SCIM PATCH allows identity providers to make partial updates to resources without sending the full representation. This is how most providers update individual attributes or manage group membership.

## How it works

A PATCH request sends a JSON body with the `PatchOp` schema and an array of operations:

```json
{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
    "Operations": [
        {
            "op": "replace",
            "path": "displayName",
            "value": "New Name"
        }
    ]
}
```

The package applies each operation to the current resource state, then validates and saves the result.

## Supported operations

### Replace

Replace the value of an attribute:

```json
{
    "op": "replace",
    "path": "displayName",
    "value": "Engineering Team"
}
```

### Add

Add values to an attribute. For multi-valued attributes (like group members), new items are appended:

```json
{
    "op": "add",
    "path": "members",
    "value": [
        {"value": "user-id-123", "display": "Jane Doe"}
    ]
}
```

For scalar attributes, the value is set (same as replace).

### Remove

Remove an attribute value entirely:

```json
{
    "op": "remove",
    "path": "members"
}
```

#### Remove with filter

Remove specific items from a multi-valued attribute using a filter expression:

```json
{
    "op": "remove",
    "path": "members[value eq \"user-id-123\"]"
}
```

This is how identity providers like Microsoft Entra remove individual members from a group.

## Filter expressions in paths

PATCH paths support filter expressions for targeting specific items within multi-valued attributes:

```
members[value eq "123"]
emails[type eq "work"].value
```

Supported filter operators in path expressions: `eq`, `ne`, `co`, `sw`, `ew`.

## Multiple operations

You can send multiple operations in a single PATCH request:

```json
{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
    "Operations": [
        {
            "op": "replace",
            "path": "displayName",
            "value": "Platform Engineering"
        },
        {
            "op": "add",
            "path": "members",
            "value": [
                {"value": "user-id-456", "display": "Bob"}
            ]
        },
        {
            "op": "remove",
            "path": "members[value eq \"user-id-123\"]"
        }
    ]
}
```

Operations are applied sequentially to the current state.

## How the package processes PATCH

1. Load the current resource from the database
2. Convert it to its SCIM array representation
3. Apply each operation to the array
4. Validate the resulting data against the schema
5. Create a new schema instance from the validated data
6. Apply changes to the model and save
7. Sync any relationships (e.g., group members)
