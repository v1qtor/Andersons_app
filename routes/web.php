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

Route::redirect('/', '/login');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
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
    });

    // Chef Management
    Route::middleware(['chef'])->group(function () {
        Route::get('chef/meals', MealPlanning::class)->name('chef.meals.index');
    });

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
    Route::delete('trips/{trip}/checkpoints/{checkpoint}/remove-image', [App\Http\Controllers\TripController::class, 'removeCheckpointImage'])->name('trips.checkpoints.remove-image');
    Route::post('trips/{trip}/checkpoints/reorder', [App\Http\Controllers\TripController::class, 'reorderCheckpointsRoute'])->name('trips.checkpoints.reorder');

    Route::post('trips/{trip}/files', [App\Http\Controllers\TripController::class, 'uploadFile'])->name('trips.files.upload');
    Route::delete('trips/{trip}/files/{file}', [App\Http\Controllers\TripController::class, 'removeFile'])->name('trips.files.remove');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('checkpoints', App\Http\Controllers\CheckpointController::class)->except(['show', 'create', 'edit']);
});