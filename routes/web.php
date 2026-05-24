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
use App\Livewire\Unavailability;
use App\Livewire\Unavailability\UnavailabilityCalendar;
use App\Livewire\Dashboard;
use App\Http\Controllers\TripController;
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
    Route::get('notifications/test', 'App\Http\Controllers\NotificationTestController@index')->name('notifications.test');
    Route::post('notifications/send-test', 'App\Http\Controllers\NotificationTestController@sendTestNotification')->name('notifications.send-test');
    Route::post('notifications/send-task-assignment', 'App\Http\Controllers\NotificationTestController@sendTaskAssignmentNotification')->name('notifications.send-task-assignment');
    Route::post('notifications/send-collaboration-request', 'App\Http\Controllers\NotificationTestController@sendCollaborationRequestNotification')->name('notifications.send-collaboration-request');
    Route::post('notifications/{id}/mark-as-read', 'App\Http\Controllers\NotificationTestController@markAsRead')->name('notifications.mark-as-read');
    Route::delete('notifications/{id}', 'App\Http\Controllers\NotificationTestController@deleteNotification')->name('notifications.delete');
    Route::post('notifications/clear-all', 'App\Http\Controllers\NotificationTestController@clearAll')->name('notifications.clear-all');

    // API-style notification endpoint for AJAX calls (session auth)
    Route::get('api/notifications', 'App\Http\Controllers\Api\NotificationController@index')->name('notifications.list');

    // Geocoding API routes
    Route::post('api/geocode-address', 'App\Http\Controllers\GeocodingController@geocodeAddress')->name('geocode.address');
    Route::post('api/reverse-geocode', 'App\Http\Controllers\GeocodingController@reverseGeocode')->name('geocode.reverse');

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

    /*
    // Unavailabilities
    Route::middleware(['unavailability'])->group(function () {
        Route::get('unavailabilities', Unavailability::class)->name('unavailabilities');
    });
    */

    // Meal Schedule (Family Member, The Andersons, Staff — and any authenticated user)
    Route::get('meals', MealSchedule::class)->name('meals.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('trips', [App\Http\Controllers\TripController::class, 'index'])->name('trips.index');
    Route::post('trips', [App\Http\Controllers\TripController::class, 'store'])->name('trips.store');
    Route::put('trips/{trip}', [App\Http\Controllers\TripController::class, 'update'])->name('trips.update');
    Route::delete('trips/{trip}', [App\Http\Controllers\TripController::class, 'destroy'])->name('trips.destroy');
    Route::post('trips/{trip}/cancel', [App\Http\Controllers\TripController::class, 'cancel'])->name('trips.cancel');

    Route::post('trips/{trip}/checkpoints', [App\Http\Controllers\TripController::class, 'addCheckpoint'])->name('trips.checkpoints.add');
    Route::delete('trips/{trip}/checkpoints/{checkpoint}', [App\Http\Controllers\TripController::class, 'removeCheckpoint'])->name('trips.checkpoints.remove');
    Route::post('trips/{trip}/checkpoints/{checkpoint}/arrive', [App\Http\Controllers\TripController::class, 'markCheckpointArrived'])->name('trips.checkpoints.arrive');
    Route::post('trips/{trip}/checkpoints/{checkpoint}/unarrive', [TripController::class, 'unmarkCheckpointArrived'])->name('trips.checkpoints.unarrive');
    Route::post('trips/{trip}/checkpoints/{checkpoint}/upload-image', [App\Http\Controllers\TripController::class, 'uploadCheckpointImage'])->name('trips.checkpoints.upload-image');
    Route::delete('trips/{trip}/checkpoints/{checkpoint}/images/{image}', [App\Http\Controllers\TripController::class, 'removeCheckpointImage'])->name('trips.checkpoints.images.remove');
    Route::post('trips/{trip}/checkpoints/reorder', [App\Http\Controllers\TripController::class, 'reorderCheckpointsRoute'])->name('trips.checkpoints.reorder');
    

    Route::post('trips/{trip}/files', [App\Http\Controllers\TripController::class, 'uploadFile'])->name('trips.files.upload');
    Route::delete('trips/{trip}/files/{file}', [App\Http\Controllers\TripController::class, 'removeFile'])->name('trips.files.remove');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('checkpoints', App\Http\Controllers\CheckpointController::class)->except(['show', 'create', 'edit']);
});