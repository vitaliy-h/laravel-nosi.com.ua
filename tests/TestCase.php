<?php

namespace Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\InteractsWithPages;
use Tests\Concerns\InteractsWithRolesAndPermissions;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithPages;
    use InteractsWithRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }
}
