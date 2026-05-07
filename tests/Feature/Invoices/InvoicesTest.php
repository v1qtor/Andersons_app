<?php

use App\Livewire\Invoices;
use App\Models\Category;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CategorySeeder::class);

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

    $this->category = Category::first();
});

test('guests are redirected from the invoices page', function () {
    $this->get(route('invoices'))->assertRedirect('/login');
});

test('family members are forbidden from the invoices page', function () {
    $this->actingAs($this->family)
        ->get(route('invoices'))
        ->assertForbidden();
});

test('staff can view the invoices page', function () {
    $this->actingAs($this->staff)
        ->get(route('invoices'))
        ->assertStatus(200);
});

test('staff only see their own invoices', function () {
    $ownInvoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
    ])->create();

    $othersInvoice = Receipt::factory()->state([
        'user_id' => $this->otherStaff->id,
        'category_id' => $this->category->id,
    ])->create();

    $invoices = Livewire::actingAs($this->staff)
        ->test(Invoices::class)
        ->instance()
        ->getInvoices();

    expect($invoices->pluck('id')->all())
        ->toContain($ownInvoice->id)
        ->not->toContain($othersInvoice->id);
});

test('admins see all invoices', function () {
    $invoiceA = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
    ])->create();

    $invoiceB = Receipt::factory()->state([
        'user_id' => $this->otherStaff->id,
        'category_id' => $this->category->id,
    ])->create();

    $invoices = Livewire::actingAs($this->admin)
        ->test(Invoices::class)
        ->instance()
        ->getInvoices();

    expect($invoices->pluck('id')->all())
        ->toContain($invoiceA->id, $invoiceB->id);
});

test('the paid status filter works', function () {
    $paid = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => true,
    ])->create();

    $unpaid = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => false,
    ])->create();

    $component = Livewire::actingAs($this->staff)
        ->test(Invoices::class)
        ->set('filterStatus', 'paid');

    $ids = $component->instance()->getInvoices()->pluck('id')->all();
    expect($ids)->toContain($paid->id)->not->toContain($unpaid->id);

    $component->set('filterStatus', 'unpaid');
    $ids = $component->instance()->getInvoices()->pluck('id')->all();
    expect($ids)->toContain($unpaid->id)->not->toContain($paid->id);
});

test('admins can search invoices by user name', function () {
    $targetUser = User::factory()->state([
        'name' => 'Findable Target Person',
        'email' => 'target@example.com',
        'role_id' => $this->staffRole->id,
    ])->create();

    $targetInvoice = Receipt::factory()->state([
        'user_id' => $targetUser->id,
        'category_id' => $this->category->id,
    ])->create();

    $otherInvoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
    ])->create();

    $invoices = Livewire::actingAs($this->admin)
        ->test(Invoices::class)
        ->set('searchName', 'Findable')
        ->instance()
        ->getInvoices();

    expect($invoices->pluck('id')->all())
        ->toContain($targetInvoice->id)
        ->not->toContain($otherInvoice->id);
});

test('staff cannot view another users invoice', function () {
    $othersInvoice = Receipt::factory()->state([
        'user_id' => $this->otherStaff->id,
        'category_id' => $this->category->id,
    ])->create();

    Livewire::actingAs($this->staff)
        ->test(Invoices::class)
        ->call('viewInvoice', $othersInvoice)
        ->assertStatus(403);
});

test('only admins can mark an invoice as paid', function () {
    $invoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => false,
        'paid_date' => null,
    ])->create();

    Livewire::actingAs($this->staff)
        ->test(Invoices::class)
        ->call('updateInvoiceStatus', $invoice->id)
        ->assertStatus(403);

    expect($invoice->fresh()->is_paid)->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(Invoices::class)
        ->call('updateInvoiceStatus', $invoice->id);

    expect($invoice->fresh()->is_paid)->toBeTrue()
        ->and($invoice->fresh()->paid_date)->not->toBeNull();
});

test('non-admins cannot delete paid invoices', function () {
    $paidInvoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => true,
    ])->create();

    Livewire::actingAs($this->staff)
        ->test(Invoices::class)
        ->call('deleteInvoice', $paidInvoice->id);

    expect(Receipt::find($paidInvoice->id))->not->toBeNull();
});

test('staff can delete their own unpaid invoices', function () {
    $invoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => false,
    ])->create();

    Livewire::actingAs($this->staff)
        ->test(Invoices::class)
        ->call('deleteInvoice', $invoice->id);

    expect(Receipt::find($invoice->id))->toBeNull();
});

test('admins can delete any invoice including paid ones', function () {
    $paidInvoice = Receipt::factory()->state([
        'user_id' => $this->staff->id,
        'category_id' => $this->category->id,
        'is_paid' => true,
    ])->create();

    Livewire::actingAs($this->admin)
        ->test(Invoices::class)
        ->call('deleteInvoice', $paidInvoice->id);

    expect(Receipt::find($paidInvoice->id))->toBeNull();
});
