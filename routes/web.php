<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\Admin\AdminAvailabilityController;

use App\Http\Controllers\UnavailabilityController;

use App\Livewire\Unavailability\UnavailabilityCalendar;
use App\Livewire\Admin\StaffAvailabilityCalendar;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('unavailability', UnavailabilityCalendar::class)->name('unavailability.index');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

// Admin routes – require authentication and admin role
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('staff-availability', StaffAvailabilityCalendar::class)->name('staff-availability');
});

require __DIR__.'/auth.php';
