<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $guard = null)
    {
        // Tests switching identities simulate distinct logins, not a stolen session.
        if (auth()->id() !== $user->getAuthIdentifier()) {
            $this->app['session']->forget(['password_hash_'.($guard ?? 'web'), 'filial_ativa_id']);
        }
        return parent::actingAs($user, $guard);
    }
}
