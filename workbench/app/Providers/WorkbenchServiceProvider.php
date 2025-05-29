<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;
use OpenSoutheners\LaravelScim\Support\SCIM;
use Workbench\App\Actions\User\CreateUserFromScim;
use Workbench\App\Actions\User\UpdateUserFromScimPutAction;
use Workbench\App\Mappers\UserScimMapper;
use Workbench\App\Models\User;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        SCIM::authenticationSchemes(ScimAuthenticationScheme::OAuthBearerToken);

        SCIM::user(
            model: User::class,
            putAction: UpdateUserFromScimPutAction::class,
            createAction: CreateUserFromScim::class,
            mapper: UserScimMapper::class
        );
    }
}
