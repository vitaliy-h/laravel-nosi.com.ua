<?php

namespace Tests\Feature\Infrastructure;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthHelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_acting_as_admin_returns_user_with_admin_permission(): void
    {
        $user = $this->actingAsAdmin();

        $this->assertTrue($user->can('admin'));
    }
}
