<?php

use App\Livewire\Admin\AdminPanel;
use App\Livewire\Admin\UserCreate;
use App\Livewire\Admin\UserEdit;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Admin\StaffAvailabilityCalendar;
use App\Livewire\InvoiceForm;
use App\Livewire\Invoices;
use App\Livewire\Meals\MealPlanning;
use App\Livewire\Meals\MealSchedule;
use App\Livewire\Schedule;
use App\Livewire\Settings;
use App\Livewire\Unavailability\UnavailabilityCalendar;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Load authentication routes
require __DIR__.'/auth.php';

Route::redirect('/', '/login');

// Broadcasting authentication route - must be before other routes and inside auth middleware
Route::middleware(['auth'])->group(function () {
    Broadcast::routes();
});

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('schedule', Schedule::class)->name('schedule');

    Route::get('settings', Settings::class)->name('settings');

    Route::get('unavailability', UnavailabilityCalendar::class)->name('unavailability');

    // Invoices/Expenses
    Route::get('invoices', Invoices::class)->name('invoices');
    Route::get('invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::get('invoices/{id}', InvoiceForm::class)->where('id', '[0-9]+')->name('invoices.edit');
    Route::get('receipts/{path}', 'App\Http\Controllers\ReceiptController@show')->where('path', '.*')->name('receipts.show');

    // Notifications
    Route::post('notifications/{id}/mark-as-read', 'App\Http\Controllers\Api\NotificationController@markAsRead')->name('notifications.mark-as-read');
    Route::delete('notifications/{id}', 'App\Http\Controllers\Api\NotificationController@destroy')->name('notifications.delete');
    Route::post('notifications/clear-all', 'App\Http\Controllers\Api\NotificationController@clearAll')->name('notifications.clear-all');

    // API-style notification endpoint for AJAX calls (session auth)
    Route::get('api/notifications', 'App\Http\Controllers\Api\NotificationController@index')->name('notifications.list');

    // Admin Management
    Route::middleware(['admin'])->group(function () {
        Route::get('admin/panel', AdminPanel::class)->name('admin.panel');
        Route::get('admin/users/create', UserCreate::class)->name('admin.users.create');
        Route::get('admin/users/{user}/edit', UserEdit::class)->name('admin.users.edit');
        Route::get('admin/meals', MealPlanning::class)->name('admin.meals.index');
        // admin-staff-unavailability management
        Route::get('admin/staff-unavailability', StaffAvailabilityCalendar::class)->name('admin.staff-unavailability'); 
    });

    // Chef Management
    Route::middleware(['chef'])->group(function () {
        Route::get('chef/meals', MealPlanning::class)->name('chef.meals.index');
    });

    // Meal Schedule (Family Member, The Andersons, Staff — and any authenticated user)
    Route::get('meals', MealSchedule::class)->name('meals.index');
});

Route::middleware(['auth'])->group(function () {
    // Trips: a Livewire component (App\Livewire\Trips\TripManager, TripFormModal,
    // TripCard) rather than a controller — all mutations (create/edit/delete/
    // cancel/checkpoints/documents) are handled as component methods.
    Route::get('trips', \App\Livewire\Trips\TripManager::class)->name('trips.index');
});

Route::middleware(['auth'])->group(function () {
    // Checkpoints library: a Livewire component (App\Livewire\Checkpoints\CheckpointManager)
    // rather than a controller — create/edit/delete are component methods.
    Route::get('checkpoints', \App\Livewire\Checkpoints\CheckpointManager::class)->name('checkpoints.index');
});