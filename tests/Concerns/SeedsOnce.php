<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Enables per-describe-block data seeding.
 *
 * Use this with the useSeed() helper function to seed data that persists
 * across all tests in a describe block. The data is seeded before the
 * database transaction starts, so it won't be rolled back after each test.
 *
 * @example
 * ```php
 * uses(SeedsOnce::class);
 *
 * // Global seed (runs before any describe-specific seeds).
 * useSeed(function () {
 *     $this->seed(RolesTableSeeder::class);
 * });
 *
 * describe('Home', function () {
 *     useSeed(function () {
 *         User::factory()->create();
 *     });
 *
 *     it('renders for guests', function () {
 *         expect(User::count())->toBe(1);
 *     });
 * });
 * ```
 */
trait SeedsOnce
{
    use RefreshDatabase {
        beginDatabaseTransaction as baseBeginDatabaseTransaction;
    }

    protected function beginDatabaseTransaction(): void
    {
        if (SeedOnceState::hasCallbacks()) {
            $describeKey = SeedOnceState::getDescribeKeyFromTestName($this->name());
            SeedOnceState::runForDescribe($describeKey, $this);
        }

        $this->baseBeginDatabaseTransaction();
    }
}
