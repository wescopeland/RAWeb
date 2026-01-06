<?php

declare(strict_types=1);

use Pest\PendingCalls\DescribeCall;
use Tests\Concerns\SeedOnceState;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Unit');

/**
 * Seed data once for the current describe block (or globally if called at file level).
 * Data persists across all tests in the block because it's committed before transactions.
 *
 * Must be used with the SeedsOnce trait:
 *
 *     uses(SeedsOnce::class);
 *
 *     // Global seed (runs before any describe-specific seeds).
 *     useSeed(function () {
 *         $this->seed(RolesTableSeeder::class);
 *     });
 *
 *     describe('Home', function () {
 *         useSeed(function () {
 *             User::factory()->create();
 *         });
 *
 *         it('renders for guests', function () {
 *             expect(User::count())->toBe(1);
 *         });
 *     });
 */
function useSeed(Closure $callback): void
{
    $describing = DescribeCall::describing();

    if (empty($describing)) {
        $key = '__global__';
    } else {
        $key = implode(' → ', array_map(fn ($d) => (string) $d, $describing));
    }

    SeedOnceState::register($key, $callback);
}
