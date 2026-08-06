<?php

namespace App\Exceptions;

use Sentry\Event;

/**
 * Class SentrySanitizer
 *
 * This class handles sanitizing and scrubbing sensitive data from exception and transaction payloads
 * before they are transmitted to Sentry.
 */
class SentrySanitizer
{
    /**
     * List of sensitive keys that should be masked.
     * Match is case-insensitive and partial (str_contains).
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'api_key',
        'apikey',
        'key',
        'token',
        'secret',
        'jwt',
        'db_password',
        'database_password',
        'credential',
        'credentials',
        'card_number',
        'cvv',
        'auth',
        'authorization',
    ];

    /**
     * Sanitize event data (errors/exceptions) before sending to Sentry.
     *
     * @param Event $event
     * @return Event|null
     */
    public static function beforeSend(Event $event): ?Event
    {
        // 1. Sanitize request details (payload, headers, cookies, query parameters)
        $request = $event->getRequest();
        if (!empty($request)) {
            $request = self::sanitizeArray($request);
            $event->setRequest($request);
        }

        // 2. Sanitize user profile information
        $user = $event->getUser();
        if ($user !== null) {
            if (method_exists($user, 'setEmail') && $user->getEmail() !== null) {
                $user->setEmail('[REDACTED]');
            }
            if (method_exists($user, 'setUsername') && $user->getUsername() !== null) {
                $user->setUsername('[REDACTED]');
            }
            // If user context has custom data, sanitize it too
            if (method_exists($user, 'getMetadata') && method_exists($user, 'setMetadata')) {
                $metadata = $user->getMetadata();
                if (!empty($metadata)) {
                    $user->setMetadata(self::sanitizeArray($metadata));
                }
            }
            $event->setUser($user);
        }

        // 3. Sanitize extra debug details attached to the event
        $extra = $event->getExtra();
        if (!empty($extra)) {
            $extra = self::sanitizeArray($extra);
            $event->setExtra($extra);
        }

        return $event;
    }

    /**
     * Sanitize transaction details (performance tracing) before sending to Sentry.
     *
     * @param Event $event
     * @return Event|null
     */
    public static function beforeSendTransaction(Event $event): ?Event
    {
        // We apply the same sanitization logic to transactions if they contain request details
        $request = $event->getRequest();
        if (!empty($request)) {
            $request = self::sanitizeArray($request);
            $event->setRequest($request);
        }

        $extra = $event->getExtra();
        if (!empty($extra)) {
            $extra = self::sanitizeArray($extra);
            $event->setExtra($extra);
        }

        return $event;
    }

    /**
     * Recursively sanitize keys in an array.
     *
     * @param array $data
     * @return array
     */
    protected static function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
            } elseif (is_string($key) && self::isSensitiveKey($key)) {
                $data[$key] = '[REDACTED]';
            }
        }
        return $data;
    }

    /**
     * Check if the given key matches any of the configured sensitive keys.
     *
     * @param string $key
     * @return bool
     */
    protected static function isSensitiveKey(string $key): bool
    {
        $normalizedKey = strtolower($key);
        foreach (self::$sensitiveKeys as $sensitiveKey) {
            if (str_contains($normalizedKey, $sensitiveKey)) {
                return true;
            }
        }
        return false;
    }
}
