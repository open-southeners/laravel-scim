<?php

namespace OpenSoutheners\LaravelScim\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Workbench\App\Models\User;

#[WithMigration]
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;
    use RefreshDatabase;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        $app['config']->set('scim', include_once __DIR__.'/../config/scim.php');
        $app['config']->set('database.connections.testing.foreign_key_constraints', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Allow all SCIM gate checks in tests
        Gate::before(fn ($user, $ability) => true);

        // Authenticate a user so Gate checks have a user context
        $this->actingAs(User::factory()->create());
    }
}
