<?php

use App\Livewire\InvoiceForm;
use App\Models\Category;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CategorySeeder::class);
    Storage::fake('public');

    $this->adminRole = Role::where('name', 'Admin')->first();
    $this->staffRole = Role::where('name', 'Staff')->first();
    $this->familyRole = Role::where('name', 'Family Member')->first();

    $this->admin = User::factory()->state([
        'email' => 'admin-test@example.com',
        'role_id' => $this->adminRole->id,
    ])->create();

    $this->staff = User::factory()->state([
        'email' => 'staff-test@example.com',
        'role_id' => $this->staffRole->id,
    ])->create();

    $this->otherStaff = User::factory()->state([
        'email' => 'other-staff@example.com',
        'role_id' => $this->staffRole->id,
    ])->create();

    $this->family = User::factory()->state([
        'email' => 'family-test@example.com',
        'role_id' => $this->familyRole->id,
    ])->create();

    $this->category = Category::where('name', '!=', 'Other')->first();
});

test('family members cannot access the invoice form', function () {
    $this->actingAs($this->family)
        ->get(route('invoices.create'))
        ->assertForbidden();
});

test('staff can create an invoice with a file upload', function () {
    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->staff)
        ->test(InvoiceForm::class)
        ->set('receiptFile', $file)
        ->set('billDate', now()->subDay()->format('Y-m-d'))
        ->set('category', (string) $this->category->id)
        ->set('description', 'Test invoice')
        ->set('amount', '123.45')
        ->call('prepareSave')
        ->assertRedirect(route('invoices'));

    $receipt = Receipt::where('user_id', $this->staff->id)->first();
    expect($receipt)->not->toBeNull()
        ->and((float) $receipt->amount)->toBe(123.45)
        ->and($receipt->category_id)->toBe($this->category->id)
        ->and($receipt->is_paid)->toBeFalse();

    Storage::disk('public')->assertExists($receipt->file_path);
});

test('creating an invoice requires a file', function () {
    Livewire::actingAs($this->staff)
        ->test(InvoiceForm::class)
        ->set('billDate', now()->subDay()->format('Y-m-d'))
        ->set('category', (string) $this->category->id)
        ->set('amount', '123.45')
        ->call('prepareSave')
        ->assertHasErrors(['receiptFile' => 'required']);
});

test('invoice amount must be within the valid range', function () {
    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->staff)
        ->test(InvoiceForm::class)
        ->set('receiptFile', $file)
        ->set('billDate', now()->subDay()->format('Y-m-d'))
        ->set('category', (string) $this->category->id)
        ->set('amount', '0')
        ->call('prepareSave')
        ->assertHasErrors('amount');
});

test('bill date cannot be in the future', function () {
    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->staff)
        ->test(InvoiceForm::class)
        ->set('receiptFile', $file)
        ->set('billDate', now()->addWeek()->format('Y-m-d'))
        ->set('category', (string) $this->category->id)
        ->set('amount', '50.00')
        ->call('prepareSave')
        ->assertHasErrors(['billDate' => 'before_or_equal']);
});

test('custom category name is required when category is other', function () {
    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    Livewire::actingAs($this->staff)
        ->test(InvoiceForm::class)
        ->set('receiptFile', $file)
        ->set('billDate', now()->subDay()->format('Y-m-d'))
        ->set('category', 'other')
        ->set('amount', '50.00')
        ->call('prepareSave')
        ->assertHasErrors(['customCategory' => 'required_if']);
});

test('non-admin users cannot load the edit form for another users invoice', function () {
    $othersInvoice = Receipt::factory()->state([
        'user_id' => $this->otherStaff->id,
        'category_id' => $this->category->id,
    ])->create();

    $this->actingAs($this->staff)
        ->get(route('invoices.edit', $othersInvoice->id))
        ->assertForbidden();
});

test('admins can edit another users invoice', function () {
    $othersInvoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => false,
        'amount' => 10.00,
    ])->create();

    Livewire::actingAs($this->admin)
        ->test(InvoiceForm::class, ['id' => $othersInvoice->id])
        ->set('amount', '999.99')
        ->call('prepareSave')
        ->assertRedirect(route('invoices'));

    expect((float) $othersInvoice->fresh()->amount)->toBe(999.99);
});

test('non-admins cannot modify a paid invoice', function () {
    $paidInvoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => true,
        'amount' => 10.00,
    ])->create();

    Livewire::actingAs($this->staff)
        ->test(InvoiceForm::class, ['id' => $paidInvoice->id])
        ->set('amount', '999.99')
        ->call('prepareSave');

    expect((float) $paidInvoice->fresh()->amount)->toBe(10.00);
});
