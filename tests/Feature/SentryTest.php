<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Class SentryTest
 *
 * Verifies Sentry test route exception behavior.
 */
class SentryTest extends TestCase
{
    /**
     * Test that the Sentry test route throws a RuntimeException.
     */
    public function test_sentry_test_route_throws_exception()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This is an intentional test exception to verify Sentry setup.');

        $this->withoutExceptionHandling();
        $this->get('/sentry-test');
    }
}
