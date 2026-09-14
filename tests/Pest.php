<?php

use App\Models\Role;
use App\Models\Trip;
use App\Models\TripCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Unit tests (e.g. Policies) work with in-memory model instances only —
// no database needed, so RefreshDatabase is skipped for speed.
pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Build an in-memory (unsaved) User with the given role name attached,
 * for Policy unit tests that only need to check role-based logic.
 */
function userWithRole(?string $roleName): User
{
    $user = new User;

    if ($roleName !== null) {
        $user->setRelation('role', (new Role)->forceFill(['name' => $roleName]));
    }

    return $user;
}

/**
 * Create a Trip for tests, defaulting to dates that resolve to "upcoming"
 * (matching Trip::resolveStatusFor's own logic) unless overridden.
 */
function makeTrip(array $overrides = []): Trip
{
    $start = $overrides['start_date'] ?? now()->addWeek();
    $end = $overrides['end_date'] ?? now()->addWeek()->addDays(2);

    return Trip::create(array_merge([
        'name' => 'Test Trip',
        'start_date' => $start,
        'end_date' => $end,
        'trip_category_id' => TripCategory::first()->id,
        'status_id' => Trip::resolveStatusFor($start, $end)?->id,
    ], $overrides));
}
