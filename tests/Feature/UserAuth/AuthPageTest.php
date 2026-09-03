<?php

namespace Tests\Feature\UserAuth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_login_form_is_the_default(): void
    {
        $this->get(save_route('auth.index'))
            ->assertStatus(200)
            ->assertViewHas('form_view', 'modules.user_auth.partials.forms.login')
            ->assertViewHas('is_login', true);
    }

    /**
     * Each form is opened by a bare query flag: /auth?register.
     */
    public function test_each_mode_flag_opens_its_own_form(): void
    {
        foreach (['login', 'register', 'password_recovery', 'password_reset'] as $mode) {
            $this->get(save_route('auth.index').'?'.$mode)
                ->assertStatus(200)
                ->assertViewHas('mode', $mode)
                ->assertViewHas('form_view', 'modules.user_auth.partials.forms.'.$mode);
        }
    }

    public function test_password_reset_form_carries_the_token_and_email(): void
    {
        $this->get(save_route('auth.index').'?password_reset&token=reset-token&email=user@example.com')
            ->assertStatus(200)
            ->assertSee('reset-token', false)
            ->assertSee('user@example.com', false);
    }

    /**
     * `mode` selects the included template, so anything outside the whitelist
     * has to fall back to the login form rather than reach `@include`.
     */
    public function test_unknown_mode_flag_falls_back_to_the_login_form(): void
    {
        $this->get(save_route('auth.index').'?'.urlencode('../../../etc/passwd'))
            ->assertStatus(200)
            ->assertViewHas('form_view', 'modules.user_auth.partials.forms.login');
    }
}
