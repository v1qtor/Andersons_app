<?php

use App\Livewire\Admin\UserCreate;
use App\Livewire\Admin\UserEdit;
use App\Livewire\Admin\UserIndex;
use App\Livewire\InvoiceForm;
use App\Livewire\Invoices;
use App\Livewire\Meals\MealPlanning;
use App\Livewire\Meals\MealSchedule;
use App\Livewire\Schedule;
use App\Livewire\Settings;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\Admin\AdminAvailabilityController;

use App\Http\Controllers\UnavailabilityController;

use App\Livewire\Unavailability\UnavailabilityCalendar;
use App\Livewire\Admin\StaffAvailabilityCalendar;

Route::redirect('/', '/login');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('unavailability', UnavailabilityCalendar::class)->name('unavailability.index');

    Route::redirect('settings', 'settings/profile');
    Route::get('schedule', Schedule::class)->name('schedule');

    Route::get('settings', Settings::class)->name('settings');

    // Invoices/Expenses
    Route::get('invoices', Invoices::class)->name('invoices');
    Route::get('invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::get('invoices/{id}', InvoiceForm::class)->where('id', '[0-9]+')->name('invoices.edit');
    Route::get('receipts/{path}', 'App\Http\Controllers\ReceiptController@show')->where('path', '.*')->name('receipts.show');


    // Admin Management
    Route::middleware(['admin'])->group(function () {
        Route::get('admin/users', UserIndex::class)->name('admin.users.index');
        Route::get('admin/users/create', UserCreate::class)->name('admin.users.create');
        Route::get('admin/users/{user}/edit', UserEdit::class)->name('admin.users.edit');
        Route::get('admin/meals', MealPlanning::class)->name('admin.meals.index');
        Route::get('admin/staff-availability', StaffAvailabilityCalendar::class)->name('admin.staff-availability');
    });

    // Chef Management
    Route::middleware(['chef'])->group(function () {
        Route::get('chef/meals', MealPlanning::class)->name('chef.meals.index');
    });

    // Meal Schedule (Family Member, The Andersons, Staff — and any authenticated user)
    Route::get('meals', MealSchedule::class)->name('meals.index');
});

require __DIR__.'/auth.php';
