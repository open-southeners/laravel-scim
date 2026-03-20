# Upgrade Guide

## From 0.1.x to 0.2.0

Version 0.2.0 is a non-breaking release. All existing code continues to work. However, some patterns are now deprecated.

### What changed

1. **Schema metadata is now cached** - Reflection and Symfony PropertyInfo are no longer created on every request. This is automatic and requires no code changes.

2. **`SchemaMapper` is deprecated** - It has been split into three focused classes:
   - `SchemaQueryResolver` - query management and response generation
   - `SchemaRequestValidator` - request validation and schema instantiation
   - `SchemaPatchOperator` - PATCH operation logic

   If you injected `SchemaMapper` directly, it still works as a thin wrapper. No action needed.

3. **`fill()` is deprecated** - A new `ScimSchema::create(array $args)` factory handles type coercion. The internal code now uses `create()`, but `fill()` still works in your constructors.

4. **Route resolution improved** - Routes are now resolved via case-insensitive slug matching instead of `Str::singular()`. Both `/scim/v2/Users` and `/scim/v2/users` work.

5. **`externalId` column is configurable** - Set `scim.external_id_column` in config or override `getExternalIdColumn()` per schema.

6. **Enterprise extension namespacing** - Attributes with `extensionUrn` are now properly grouped under their URN key in JSON output.

### New config keys

Add these to your `config/scim.php` if you published the config previously:

```php
'external_id_column' => 'external_id',
```

Or re-publish the config:

```bash
php artisan vendor:publish --tag=laravel-scim --force
```

### Optional migration: promoted properties

Your schema constructors can optionally be simplified. This is **not required** - the old pattern works indefinitely.

```php
// Before (still works)
public function __construct(
    #[ScimSchemaAttribute(modelAttribute: 'email')]
    string $userName,
) {
    $this->fill(func_get_args());
}

// After (optional, removes fill dependency)
// Note: promoted property types must match parent class types
public function __construct(
    #[ScimSchemaAttribute(modelAttribute: 'email')]
    public ?string $userName = null,
) {}
```

{% hint style="warning" %}
When using promoted properties, the type must match the parent class property declaration. If the parent declares `public ?string $userName`, the child must also use `?string`, not `string`.
{% endhint %}
