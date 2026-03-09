<div class="w-full max-w-4xl mx-auto">
    <!-- Success Message Toast -->
    @if (session('status') || session('message') || session('error'))
        <script>
            setTimeout(() => {
                const toast = document.getElementById('success-toast');
                if (toast) {
                    toast.style.animation = 'slideOut 0.3s ease-out forwards';
                }
            }, 3000);
        </script>
        <div id="success-toast" class="fixed top-4 right-4 @if(session('error')) bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 @else bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 @endif border rounded-lg shadow-lg p-4 max-w-md z-50" style="animation: slideIn 0.3s ease-out;">
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
        </style>
    @endif

    <div class="space-y-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $invoice ? 'Edit Invoice' : 'Submit Invoices' }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $invoice ? 'Update invoice details.' : 'Document your purchases and submit for reimbursement' }}
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-gray-200 dark:border-neutral-700 p-8">
            <form wire:submit="prepareSave" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bill Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Invoice Date <span class="text-red-600">*</span>
                        </label>
                        <input
                            wire:model="billDate"
                            type="date"
                            max="{{ now()->format('Y-m-d') }}"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('billDate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Amount (£) <span class="text-red-600">*</span>
                        </label>
                        <input
                            wire:model="amount"
                            type="text"
                            inputmode="decimal"
                            placeholder="0.00"
                            @input="$event.target.value = $event.target.value.replace(/[^0-9.]/g, '')"
                            @blur="
                                let val = $event.target.value;
                                $event.target.value = Number(val).toLocaleString('en-GB', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                @this.set('amount', val);
                            "
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('amount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Category <span class="text-red-600">*</span>
                    </label>
                    <select
                        wire:model.live="category"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    >
                        <option value="">Select a category...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                        <option value="other">Other (specify below)</option>
                    </select>
                    @error('category') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Custom Category (shown when "Other" is selected) -->
                @if($category === 'other')
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Specify Category <span class="text-red-600">*</span>
                        </label>
                        <input
                            wire:model="customCategory"
                            type="text"
                            placeholder="Enter your custom category name"
                            class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        >
                        @error('customCategory') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Description <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                    </label>
                    <textarea
                        wire:model="description"
                        rows="4"
                        placeholder="Enter details about this expense (optional)"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    ></textarea>
                    @error('description') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Receipt File Upload (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                        Receipt / Proof of Purchase <span class="text-gray-500 text-xs font-normal">(Optional)</span>
                    </label>
                    <div class="relative border-2 border-dashed border-gray-300 dark:border-neutral-600 rounded-lg p-8 text-center cursor-pointer hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/10 transition-colors" onclick="document.getElementById('receipt-input').click()">
                        <input
                            id="receipt-input"
                            wire:model="receiptFile"
                            type="file"
                            class="hidden"
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-8-12v12m-4-8l4 4m4-4l-4 4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        @if($receiptFile)
                            <p class="text-sm font-medium text-green-600 dark:text-green-400">{{ $receiptFile->getClientOriginalName() }}</p>
                            <p class="text-xs text-gray-500 mt-1">Ready to upload</p>
                        @else
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-500 mt-1">PDF, JPG, or PNG (Max 5MB)</p>
                        @endif
                    </div>
                    @if($invoice && $invoice->file_path && !$receiptFile)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Current receipt: {{ basename($invoice->file_path) }}</p>
                    @endif
                    @error('receiptFile') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-neutral-700">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold"
                    >
                        {{ $invoice ? 'Update Invoices' : 'Submit Invoices' }}
                    </button>
                    <a href="{{ route('invoices') }}" class="px-6 py-3 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors font-semibold">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
