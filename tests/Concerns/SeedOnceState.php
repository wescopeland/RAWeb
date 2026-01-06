<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Closure;

/**
 * Registry for useSeed() callbacks.
 *
 * Supports per-describe-block seeding where each describe block can have
 * its own seeded data that persists across tests within that block.
 */
final class SeedOnceState
{
    /** @var array<string, Closure> */
    private static array $callbacks = [];

    /** @var array<string, bool> */
    private static array $seeded = [];

    public static function register(string $key, Closure $callback): void
    {
        self::$callbacks[$key] = $callback;
    }

    public static function runForDescribe(string $describeKey, object $testCase): void
    {
        if (!isset(self::$seeded['__global__']) && isset(self::$callbacks['__global__'])) {
            self::$callbacks['__global__']->call($testCase);
            self::$seeded['__global__'] = true;
        }

        if ($describeKey !== '__global__' && !isset(self::$seeded[$describeKey])) {
            if (isset(self::$callbacks[$describeKey])) {
                self::$callbacks[$describeKey]->call($testCase);
            }
            self::$seeded[$describeKey] = true;
        }
    }

    public static function getDescribeKeyFromTestName(string $testName): string
    {
        $name = preg_replace('/^__pest_evaluable_/', '', $testName);
        if ($name === null || $name === $testName) {
            return '__global__';
        }

        $name = str_replace('__', "\x00", $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace("\x00", '_', $name);
        $name = trim($name);

        $parts = explode('_→ ', $name);
        if (count($parts) < 2) {
            return '__global__';
        }

        array_pop($parts);

        return implode(' → ', $parts);
    }

    public static function hasCallbacks(): bool
    {
        return count(self::$callbacks) > 0;
    }

    public static function reset(): void
    {
        self::$callbacks = [];
        self::$seeded = [];
    }
}
