<div class="w-full max-w-7xl mx-auto" x-data x-effect="document.body.style.overflow = @if($viewInvoice) 'hidden' @else '' @endif">
    <!-- Success Message Toast with Alpine.js for auto-dismiss -->
    @if (session('status') || session('message') || session('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => { show = false }, 3000)" x-show="show"
             class="fixed top-4 right-4 @if(session('error')) bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 @else bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 @endif border rounded-lg shadow-lg p-4 max-w-md z-50"
             style="animation: slideIn 0.3s ease-out;"
             @change="show = true; setTimeout(() => { show = false }, 3000)">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 @if(session('error')) text-red-600 dark:text-red-400 @else text-green-600 dark:text-green-400 @endif flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="@if(session('error')) text-red-800 dark:text-red-300 @else text-green-800 dark:text-green-300 @endif font-medium">{{ session('status') ?? session('message') ?? session('error') }}</p>
            </div>
        </div>
        <style>
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
            [x-cloak] { display: none; }
        </style>
    @endif

    <!-- Status Update Confirmation Modal -->
    @if($updateStatusInvoiceId)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-800 rounded-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Mark as Paid</h2>
                </div>

                <div class="p-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-6">Are you sure you want to mark this invoice as paid?</p>
                </div>

                <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-neutral-700">
                    <button wire:click="$set('updateStatusInvoiceId', null)" class="flex-1 px-4 py-2 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors font-medium">
                        Cancel
                    </button>
                    <button wire:click="updateInvoiceStatus({{ $updateStatusInvoiceId }})" class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors font-medium">
                        Mark Paid
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($deleteInvoiceId)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-neutral-800 rounded-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Delete Invoice</h2>
                </div>

                <div class="p-6">
                    <p class="text-gray-700 dark:text-gray-300 mb-6">Are you sure you want to delete this invoice? This action cannot be undone.</p>
                </div>

                <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-neutral-700">
                    <button wire:click="$set('deleteInvoiceId', null)" class="flex-1 px-4 py-2 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors font-medium">
                        Cancel
                    </button>
                    <button wire:click="deleteInvoice({{ $deleteInvoiceId }})" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Invoice Modal -->
    @if($viewInvoice)
        <div class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 flex items-center justify-center p-4" @click.self="$wire.closeView()" @keydown.escape.window="$wire.closeView()">
            <div class="bg-white dark:bg-neutral-800 rounded-xl max-w-2xl w-full max-h-96 overflow-y-auto">
                <div class="sticky top-0 bg-white dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700 p-6 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Expense Report Details</h2>
                    <button wire:click="closeView" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Submission Date</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $viewInvoice->bill_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Amount</p>
                            <p class="font-semibold text-gray-900 dark:text-white">£{{ number_format($viewInvoice->amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Category</p>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                @if($viewInvoice->substitute_category)
                                    <span class="italic">{{ $viewInvoice->substitute_category }}</span>
                                @elseif($viewInvoice->category)
                                    {{ $viewInvoice->category->name }}
                                @else
                                    Other
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold @if($viewInvoice->is_paid) bg-green-200 text-green-900 dark:bg-green-800 dark:text-green-100 @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
                                @if($viewInvoice->is_paid)
                                    ✓ Paid
                                @else
                                    Pending
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($isAdmin && $viewInvoice->user)
                        <div class="border-t border-gray-200 dark:border-neutral-700 pt-4">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Submitted By</p>
                            <p class="text-gray-900 dark:text-gray-100">{{ $viewInvoice->user->name }}</p>
                        </div>
                    @endif

                    @if($viewInvoice->description)
                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Description</p>
                            <p class="text-gray-700 dark:text-gray-300">{{ $viewInvoice->description }}</p>
                        </div>
                    @endif

                    @if($viewInvoice->file_path)
                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Receipt / Proof of Purchase</p>
                            <a href="{{ route('receipts.show', ['path' => str_replace('receipts/', '', $viewInvoice->file_path)]) }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline">
                                View Receipt
                            </a>
                        </div>
                    @else
                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Receipt / Proof of Purchase</p>
                            <p class="text-gray-500 dark:text-gray-400 italic">No attached receipt</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-8">
        <!-- Page Header with Action Button -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ $isAdmin ? 'Expense Review' : 'Invoices' }}</h1>
                <p class="text-gray-600 dark:text-gray-400">{{ $isAdmin ? 'Review and process submitted invoices' : 'Track your submitted invoices and reimbursement status' }}</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold">
                + Submit Invoices
            </a>
        </div>

        <!-- Admin View: Search and Filters -->
        @if($isAdmin)
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search by Staff Name</label>
                        <input type="text" wire:model.live.debounce.300ms="searchName" placeholder="Search staff member..." class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Status</label>
                        <select wire:model.live="filterStatus" class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Date</label>
                        <input type="date" wire:model.live="filterDate" class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex items-end">
                        <button wire:click="clearFilters" class="w-full px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors">
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Staff View: Basic Filters -->
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Status</label>
                        <select wire:model.live="filterStatus" class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Date</label>
                        <input type="date" wire:model.live="filterDate" class="w-full px-4 py-2 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="flex items-end">
                        <button wire:click="clearFilters" class="w-full px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-neutral-700 rounded-lg hover:bg-gray-200 dark:hover:bg-neutral-600 transition-colors">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($invoices->isEmpty())
            <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No expense reports found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $isAdmin ? 'No invoices to review.' : 'Get started by submitting your first expense report for reimbursement.' }}</p>
                @if(!$isAdmin)
                    <a href="{{ route('invoices.create') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold">
                        Submit First Expense Report
                    </a>
                @endif
            </div>
        @else
            @if($isAdmin)
                <!-- Admin View: Card-based Layout -->
                <div class="space-y-4">
                    @foreach($invoices as $invoice)
                        <div class="rounded-xl shadow-sm p-6 hover:shadow-md transition-shadow @if($invoice->is_paid) bg-green-50 dark:bg-green-900/10 border border-green-300 dark:border-green-700 @else bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 @endif">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                <!-- Left Column: Details -->
                                <div class="space-y-4">
                                    <!-- Header with Status -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $invoice->user->name ?? 'Unknown' }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->bill_date->format('M d, Y') }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold @if($invoice->is_paid) bg-green-200 text-green-900 dark:bg-green-800 dark:text-green-100 @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
                                            @if($invoice->is_paid)
                                                ✓ Paid
                                            @else
                                                Pending
                                            @endif
                                        </span>
                                    </div>

                                    <!-- Details Grid -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Amount</p>
                                            <p class="text-2xl font-bold text-gray-900 dark:text-white">£{{ number_format($invoice->amount, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Category</p>
                                            <p class="text-base font-semibold text-gray-900 dark:text-white">
                                                @if($invoice->substitute_category)
                                                    <span class="italic">{{ $invoice->substitute_category }}</span>
                                                @elseif($invoice->category)
                                                    {{ $invoice->category->name }}
                                                @else
                                                    Other
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    @if($invoice->description)
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Description</p>
                                            <p class="text-gray-700 dark:text-gray-300">{{ $invoice->description }}</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Right Column: IBAN & Actions -->
                                <div class="space-y-4">
                                    <!-- IBAN Section -->
                                    @if($invoice->user && $invoice->user->iban)
                                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-sm font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wide">IBAN</p>
                                                <button wire:click="toggleShowIban({{ $invoice->id }})" class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                                    {{ $showIbanInvoiceId === $invoice->id ? 'Hide' : 'Show' }}
                                                </button>
                                            </div>
                                            @if($showIbanInvoiceId === $invoice->id)
                                                <p class="text-2xl font-mono font-bold text-gray-900 dark:text-white break-all">{{ $invoice->user->iban }}</p>
                                            @else
                                                <p class="text-lg text-gray-600 dark:text-gray-400 font-mono">•••• •••• •••• {{ substr($invoice->user->iban, -4) }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bg-gray-50 dark:bg-neutral-700 border border-gray-200 dark:border-neutral-600 rounded-lg p-4">
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">IBAN</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">No IBAN available</p>
                                        </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2 pt-2">
                                        <flux:button variant="ghost" size="sm" wire:click="viewInvoice({{ $invoice->id }})" icon="eye" class="flex-1">
                                            View Details
                                        </flux:button>
                                        @if(!$invoice->is_paid)
                                            <flux:button variant="primary" size="sm" wire:click="confirmStatusUpdate({{ $invoice->id }})" icon="check-circle" class="flex-1">
                                                Mark Paid
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Divider with Receipt and Actions -->
                            @if($invoice->file_path)
                                <div class="border-t border-gray-200 dark:border-neutral-700 pt-4 flex items-center justify-between">
                                    <!-- Edit/Delete buttons (admin can edit and delete any invoice) -->
                                    <div class="flex gap-1">
                                        <flux:tooltip content="{{ __('Edit') }}" position="top">
                                            <flux:button variant="ghost" size="sm" :href="route('invoices.edit', $invoice->id)" wire:navigate icon="pencil" />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ __('Delete') }}" position="top">
                                            <flux:button variant="ghost" size="sm" wire:click="$set('deleteInvoiceId', {{ $invoice->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                        </flux:tooltip>
                                    </div>
                                    <a href="{{ route('receipts.show', ['path' => str_replace('receipts/', '', $invoice->file_path)]) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        View Receipt
                                    </a>
                                </div>
                            @else
                                <div class="border-t border-gray-200 dark:border-neutral-700 pt-4 flex items-center justify-start">
                                    <!-- Edit/Delete buttons (admin can edit and delete any invoice) -->
                                    <div class="flex gap-1">
                                        <flux:tooltip content="{{ __('Edit') }}" position="top">
                                            <flux:button variant="ghost" size="sm" :href="route('invoices.edit', $invoice->id)" wire:navigate icon="pencil" />
                                        </flux:tooltip>
                                        <flux:tooltip content="{{ __('Delete') }}" position="top">
                                            <flux:button variant="ghost" size="sm" wire:click="$set('deleteInvoiceId', {{ $invoice->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                        </flux:tooltip>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Staff View: Table Layout -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Submission Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Category</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Description</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Amount</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @foreach($invoices as $invoice)
                                    <tr class="transition-colors @if($invoice->is_paid) bg-green-100 dark:bg-green-900/20 hover:bg-green-200 dark:hover:bg-green-900/30 @else hover:bg-gray-50 dark:hover:bg-neutral-700/50 @endif">
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            {{ $invoice->bill_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            @if($invoice->substitute_category)
                                                <span class="italic">{{ $invoice->substitute_category }}</span>
                                            @elseif($invoice->category)
                                                {{ $invoice->category->name }}
                                            @else
                                                <span class="text-gray-500">Other</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                            {{ $invoice->description ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-300">
                                            £{{ number_format($invoice->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold @if($invoice->is_paid) bg-green-200 text-green-900 dark:bg-green-800 dark:text-green-100 @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 @endif">
                                                @if($invoice->is_paid)
                                                    ✓ Paid
                                                @else
                                                    Pending
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div class="flex items-center justify-end gap-1">
                                                <flux:tooltip content="{{ __('View') }}" position="top">
                                                    <flux:button variant="ghost" size="sm" wire:click="viewInvoice({{ $invoice->id }})" icon="eye" />
                                                </flux:tooltip>
                                                @if(!$invoice->is_paid || $isAdmin)
                                                    <flux:tooltip content="{{ __('Edit') }}" position="top">
                                                        <flux:button variant="ghost" size="sm" :href="route('invoices.edit', $invoice->id)" wire:navigate icon="pencil" />
                                                    </flux:tooltip>
                                                    <flux:tooltip content="{{ __('Delete') }}" position="top">
                                                        <flux:button variant="ghost" size="sm" wire:click="$set('deleteInvoiceId', {{ $invoice->id }})" icon="trash" class="!text-red-600 hover:!text-red-700 dark:!text-red-400" />
                                                    </flux:tooltip>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Total Reports</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $invoices->count() }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Pending Total</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">£{{ number_format($invoices->where('is_paid', false)->sum('amount'), 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Paid Amount (This Month)</p>
                        <p class="text-3xl font-bold text-green-700 dark:text-green-400">£{{ number_format($thisMonthPaidTotal, 2) }}</p>
                    </div>
                </div>
            @endif

            <!-- Admin View: Summary Cards -->
            @if($isAdmin)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Total Invoices</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $invoices->count() }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Pending</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $invoices->where('is_paid', false)->count() }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Total Pending Amount</p>
                        <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">£{{ number_format($invoices->where('is_paid', false)->sum('amount'), 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-6">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Total Paid Amount (This Month)</p>
                        <p class="text-3xl font-bold text-green-700 dark:text-green-400">£{{ number_format($thisMonthPaidTotal, 2) }}</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
