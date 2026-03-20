# Installation

## Requirements

* PHP 8.2+
* Laravel 11 or 12

## Install via Composer

```bash
composer require open-southeners/laravel-scim
```

The package auto-discovers its service provider. No manual registration needed.

## Publish the config

```bash
php artisan vendor:publish --tag=laravel-scim
```

This creates `config/scim.php` where you can configure middleware, filtering limits, and more.

## Database setup

SCIM resources map to your existing Eloquent models. The only addition most apps need is an `external_id` column on tables that will be provisioned:

```bash
php artisan make:migration add_external_id_to_users_table
```

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->ulid('external_id')->nullable()->index();
    });
}
```

The `externalId` is how identity providers track their own identifier for a resource in your system. Add it to your model's `$fillable` array:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'external_id',
];
```

{% hint style="info" %}
If your column is named something other than `external_id`, set `external_id_column` in your config. See [Configuration](configuration.md).
{% endhint %}
