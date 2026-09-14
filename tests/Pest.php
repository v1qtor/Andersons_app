<?php

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

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

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
 * Create a Trip for tests, defaulting to dates that resolve to "upcoming"
 * (matching Trip::resolveStatusFor's own logic) unless overridden.
 */
function makeTrip(array $overrides = []): \App\Models\Trip
{
    $start = $overrides['start_date'] ?? now()->addWeek();
    $end = $overrides['end_date'] ?? now()->addWeek()->addDays(2);

    return \App\Models\Trip::create(array_merge([
        'name' => 'Test Trip',
        'start_date' => $start,
        'end_date' => $end,
        'trip_category_id' => \App\Models\TripCategory::first()->id,
        'status_id' => \App\Models\Trip::resolveStatusFor($start, $end)?->id,
    ], $overrides));
}
