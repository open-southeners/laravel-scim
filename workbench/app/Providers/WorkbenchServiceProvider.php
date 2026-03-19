<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenSoutheners\LaravelScim\Enums\ScimAuthenticationScheme;
use OpenSoutheners\LaravelScim\Repository;
use OpenSoutheners\LaravelScim\Support\SCIM;
use Workbench\App\Models\Group;
use Workbench\App\Models\User;
use Workbench\App\SCIM\GroupScimSchema;
use Workbench\App\SCIM\UserScimSchema;

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

        $repository = app(Repository::class);
        $repository->add(User::class, UserScimSchema::class);
        $repository->add(Group::class, GroupScimSchema::class);
    }
}
