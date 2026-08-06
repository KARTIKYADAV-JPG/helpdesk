<?php

namespace App\Http\Controllers;

use RuntimeException;

/**
 * Class SentryTestController
 *
 * Dedicated controller for testing Sentry integration.
 */
class SentryTestController extends Controller
{
    /**
     * Intentionally throw a RuntimeException to verify Sentry exception reporting.
     *
     * @throws RuntimeException
     */
    public function trigger()
    {
        throw new RuntimeException('This is an intentional test exception to verify Sentry setup.');
    }
}
