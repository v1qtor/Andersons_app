<?php

namespace App\Livewire\Invoices;

use App\Models\Receipt;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class InvoiceForm extends Component
{
    use WithFileUploads;

    public ?Receipt $invoice = null;
    public $receiptFile;
    public string $billDate = '';
    public string $category = '';
    public string $customCategory = '';
    public string $description = '';
    public string $invoiceName = '';
    public string $amount = '';

    public function mount(?int $id = null)
    {
        // Check if user has access to invoices feature
        $user = Auth::user();
        $allowedRoles = ['Staff', 'Chef', 'Admin', 'The Andersons'];
        if (!$user || !$user->role || !in_array($user->role->name, $allowedRoles)) {
            abort(403, __('Unauthorized. Staff access required.'));
        }

        if ($id) {
            $invoice = Receipt::find($id);
            if (!$invoice) {
                abort(404, 'Invoice not found');
            }
            $this->invoice = $invoice;

            if ($invoice->user_id !== Auth::id()) {
                abort(403);
            }

            $this->billDate = $invoice->bill_date->format('Y-m-d');
            // Check if this is a custom category by looking for substitute_category
            if ($invoice->substitute_category) {
                $this->category = 'other';
                $this->customCategory = $invoice->substitute_category;
            } else {
                $this->category = (string) $invoice->category_id;
                $this->customCategory = '';
            }
            $this->description = $invoice->description ?? '';
            $this->amount = number_format($invoice->amount, 2);
        }
    }

    public function prepareSave()
    {
        // Directly save for both create and edit
        $this->saveInvoice();
    }

    public function saveInvoice()
    {
        // Convert British format to dot-decimal for storage (e.g., "1,234.56" -> 1234.56)
        $amountValue = str_replace(',', '', $this->amount);
        
        // Validate amount is numeric
        if (!is_numeric($amountValue) || floatval($amountValue) < 0.01 || floatval($amountValue) > 999999.99) {
            $this->addError('amount', 'The amount must be between 0.01 and 999,999.99');
            return;
        }
        
        $this->validate([
            'receiptFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'billDate' => 'required|date|before_or_equal:today',
            'category' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'customCategory' => 'required_if:category,other|string|max:255',
        ]);

        // Get the "Other" category ID for custom categories
        $otherCategory = Category::where('name', 'Other')->first();
        
        if ($this->category === 'other' && !$otherCategory) {
            $this->addError('category', 'Other category not found in system.');
            return;
        }

        $categoryId = $this->category === 'other' ? $otherCategory->id : $this->category;
        $substituteCategory = $this->category === 'other' ? $this->customCategory : null;

        $receiptFilePath = null;
        if ($this->receiptFile) {
            // Delete old receipt file if exists
            if ($this->invoice && $this->invoice->file_path) {
                Storage::disk('public')->delete($this->invoice->file_path);
            }

            $receiptFilePath = $this->receiptFile->store('receipts', 'public');
        } elseif ($this->invoice) {
            $receiptFilePath = $this->invoice->file_path;
        }

        if ($this->invoice) {
            // Update existing invoice
            if ($this->invoice->is_paid) {
                session()->flash('error', 'Cannot modify a paid invoice.');
                return;
            }

            $this->invoice->update([
                'category_id' => $categoryId,
                'bill_date' => $this->billDate,
                'description' => $this->description,
                'substitute_category' => $substituteCategory,
                'amount' => $amountValue,
                'file_path' => $receiptFilePath ?? $this->invoice->file_path,
            ]);

            session()->flash('message', 'Invoice updated successfully.');
        } else {
            // Create new invoice
            Receipt::create([
                'user_id' => Auth::id(),
                'category_id' => $categoryId,
                'bill_date' => $this->billDate,
                'description' => $this->description,
                'substitute_category' => $substituteCategory,
                'amount' => $amountValue,
                'file_path' => $receiptFilePath,
                'upload_date' => now(),
                'is_paid' => false,
            ]);

            session()->flash('message', 'Invoice submitted successfully.');
        }

        return redirect()->route('invoices');
    }

    #[On('resetForm')]
    public function resetForm()
    {
        $this->reset(['receiptFile', 'billDate', 'category', 'customCategory', 'description', 'invoiceName', 'amount']);
    }

    public function render()
    {
        return view('livewire.invoices.invoice-form', [
            'categories' => Category::whereNotIn('name', ['Other', 'other'])->get(),
        ]);
    }
}
