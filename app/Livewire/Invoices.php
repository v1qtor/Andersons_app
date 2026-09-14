<?php

namespace App\Livewire;

use App\Models\Receipt;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Invoices extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $filterDate = '';
    public string $searchName = '';
    public ?int $viewInvoiceId = null;
    public ?int $deleteInvoiceId = null;
    public ?int $updateStatusInvoiceId = null;
    public ?int $showIbanInvoiceId = null;

    public function mount()
    {
        $this->authorize('viewAny', Receipt::class);
    }

    public function updated_filterStatus()
    {
        $this->resetPage();
    }

    public function updated_filterDate()
    {
        $this->resetPage();
    }

    public function updated_searchName()
    {
        $this->resetPage();
    }

    // Not an authorization check itself — used to pick which view mode to
    // render (a management view vs. a personal one). Actual permission
    // checks for viewing/editing/deleting a specific invoice go through
    // ReceiptPolicy (see viewInvoice, deleteInvoice, updateInvoiceStatus).
    public function isAdmin()
    {
        return (bool) Auth::user()?->isHouseholdAdmin();
    }

    public function getInvoices()
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin();

        // Admin/TheAndersons see all invoices, others see only their own
        $query = $isAdmin 
            ? Receipt::with(['category', 'user'])
            : Receipt::where('user_id', $user->id)->with(['category', 'user']);

        if ($this->filterStatus) {
            $query->where('is_paid', $this->filterStatus === 'paid');
        }

        if ($this->filterDate) {
            $query->whereDate('bill_date', $this->filterDate);
        }

        if ($isAdmin && $this->searchName) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->searchName . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function viewInvoice(Receipt $invoice)
    {
        $this->authorize('view', $invoice);
        $this->viewInvoiceId = $invoice->id;
    }

    public function closeView()
    {
        $this->viewInvoiceId = null;
    }

    public function editInvoice(Receipt $invoice)
    {
        return redirect()->route('invoices.edit', $invoice->id);
    }

    public function deleteInvoice($invoiceId)
    {
        $invoice = Receipt::find($invoiceId);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $this->authorize('view', $invoice);

        // Owners can see their paid invoices but can no longer delete them
        // once paid — a softer, friendlier stop than a hard 403 page.
        if (Auth::user()->cannot('delete', $invoice)) {
            session()->flash('error', 'Cannot delete a paid invoice.');
            $this->deleteInvoiceId = null;
            return;
        }

        $invoice->delete();
        session()->flash('message', 'Invoice deleted successfully.');
        $this->deleteInvoiceId = null;
    }

    public function toggleShowIban($invoiceId)
    {
        if ($this->showIbanInvoiceId === $invoiceId) {
            $this->showIbanInvoiceId = null;
        } else {
            $this->showIbanInvoiceId = $invoiceId;
        }
    }

    public function confirmStatusUpdate($invoiceId)
    {
        $this->updateStatusInvoiceId = $invoiceId;
    }

    public function closeStatusUpdateModal()
    {
        $this->updateStatusInvoiceId = null;
    }

    public function confirmCurrentStatusUpdate()
    {
        if ($this->updateStatusInvoiceId) {
            $this->updateInvoiceStatus($this->updateStatusInvoiceId);
        }
    }

    public function updateInvoiceStatus($invoiceId)
    {
        $invoice = Receipt::find($invoiceId);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $this->authorize('markPaid', $invoice);

        $invoice->update(['is_paid' => true, 'paid_date' => now()]);

        // Notify the invoice creator (staff member who submitted it) - check preference first
        if ($invoice->user && $this->userHasInvoiceNotificationsEnabled($invoice->user)) {
            $admin = Auth::user();
            $notification = UserNotification::create([
                'user_id' => $invoice->user->id,
                'from_user_id' => $admin->id,
                'title' => 'Invoice Approved',
                'message' => $admin->name . ' approved your invoice for £' . number_format($invoice->amount, 2),
                'type' => 'invoice_paid',
                'action_url' => '/invoices',
            ]);

            broadcast(new \App\Events\NotificationCreated($notification));
        }

        session()->flash('message', 'Invoice marked as paid successfully.');
        $this->updateStatusInvoiceId = null;
    }

    /**
     * Check if user has invoice notifications enabled
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
            return filter_var($setting->pivot->value, FILTER_VALIDATE_BOOLEAN);
        } catch (\Exception $e) {
            return true; // Default to enabled if decode fails
        }
    }

    public function closeDeleteModal()
    {
        $this->deleteInvoiceId = null;
    }

    public function confirmCurrentDelete()
    {
        if ($this->deleteInvoiceId) {
            $this->deleteInvoice($this->deleteInvoiceId);
        }
    }

    public function clearFilters()
    {
        $this->filterStatus = '';
        $this->filterDate = '';
        $this->searchName = '';
        $this->resetPage();
    }

    /**
     * Get total invoice counts for summary cards
     */
    private function getInvoiceTotals()
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin();

        $query = $isAdmin 
            ? Receipt::query()
            : Receipt::where('user_id', $user->id);

        if ($this->filterStatus) {
            $query->where('is_paid', $this->filterStatus === 'paid');
        }

        if ($this->filterDate) {
            $query->whereDate('bill_date', $this->filterDate);
        }

        if ($isAdmin && $this->searchName) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->searchName . '%');
            });
        }

        $totalCount = $query->count();
        $pendingCount = $query->where('is_paid', false)->count();
        $pendingTotal = $query->where('is_paid', false)->sum('amount');

        return [
            'total' => $totalCount,
            'pending' => $pendingCount,
            'pendingAmount' => $pendingTotal,
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $invoices = $this->getInvoices();
        $totals = $this->getInvoiceTotals();
        $viewInvoice = $this->viewInvoiceId ? Receipt::find($this->viewInvoiceId) : null;
        
        // Calculate this month's paid total
        $now = now();
        $isAdmin = $this->isAdmin();
        
        // Use date comparison instead of datetime to avoid timezone issues
        $paidQuery = Receipt::whereYear('paid_date', $now->year)
            ->whereMonth('paid_date', $now->month)
            ->where('is_paid', true);
        
        // Non-admin users only see their own invoices
        if (!$isAdmin) {
            $paidQuery->where('user_id', $user->id);
        }
        
        $thisMonthPaidTotal = $paidQuery->sum('amount');

        return view('livewire.invoices', [
            'invoices' => $invoices,
            'viewInvoice' => $viewInvoice,
            'isAdmin' => $isAdmin,
            'thisMonthPaidTotal' => $thisMonthPaidTotal,
            'totals' => $totals,
        ]);
    }
}
