<?php

namespace App\Livewire;

use App\Models\Receipt;
use App\Models\Category;
use App\Models\UserNotification;
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

            // Allow edit if: user is the owner OR user is an admin
            $isAdmin = $user->role && in_array($user->role->name, ['Admin', 'The Andersons']);
            if ($invoice->user_id !== Auth::id() && !$isAdmin) {
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
        
        // When creating, receipt is required. When editing, receipt is optional (can keep existing)
        $receiptValidation = $this->invoice ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120' : 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        
        $this->validate([
            'receiptFile' => $receiptValidation,
            'billDate' => 'required|date|before_or_equal:today',
            'category' => 'required|string',
            'description' => 'nullable|string|max:500',
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
            // Admins can modify any invoice, but regular users cannot modify paid invoices
            $user = Auth::user();
            $isAdmin = $user->role && in_array($user->role->name, ['Admin', 'The Andersons']);
            if ($this->invoice->is_paid && !$isAdmin) {
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

            // Notify invoice owner if admin makes changes - check preference first
            if ($isAdmin && $this->invoice->user_id !== $user->id && $this->userHasInvoiceNotificationsEnabled($this->invoice->user)) {
                $notification = UserNotification::create([
                    'user_id' => $this->invoice->user_id,
                    'from_user_id' => $user->id,
                    'title' => 'Invoice Modified',
                    'message' => $user->name . ' updated your invoice for £' . number_format($this->invoice->amount, 2),
                    'type' => 'invoice_changed',
                    'action_url' => '/invoices',
                ]);

                broadcast(new \App\Events\NotificationCreated($notification));
            }

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
        return view('livewire.invoice-form', [
            'categories' => Category::whereNotIn('name', ['Other', 'other'])->get(),
        ]);
    }

    /**
     * Check if user has invoice notifications (popup) enabled
     */
    private function userHasInvoiceNotificationsEnabled($user): bool
    {
        $setting = $user->notificationSettings()
            ->where('notification_type_id', 3) // receiptApprovals = id 3
            ->first();

        if (!$setting) {
            return true; // Default to enabled if not set
        }

        try {
            $preferences = json_decode($setting->pivot->value, true);
            return $preferences['popup'] ?? true;
        } catch (\Exception $e) {
            return true; // Default to enabled if decode fails
        }
    }
}
