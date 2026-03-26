<?php

namespace App\Livewire;

use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Auth\Access\AuthorizationException;

#[Layout('components.layouts.app')]
class Invoices extends Component
{
    public string $filterStatus = '';
    public string $filterDate = '';
    public string $searchName = '';
    public ?int $viewInvoiceId = null;
    public ?int $deleteInvoiceId = null;
    public ?int $updateStatusInvoiceId = null;
    public ?int $showIbanInvoiceId = null;

    public function mount()
    {
        $user = Auth::user();
        $allowedRoles = ['Staff', 'Chef', 'Admin', 'The Andersons'];
        
        if (!$user || !$user->role || !in_array($user->role->name, $allowedRoles)) {
            abort(403, __('Unauthorized. Staff access required.'));
        }
    }

    public function isAdmin()
    {
        $user = Auth::user();
        return $user && $user->role && in_array($user->role->name, ['Admin', 'The Andersons']);
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

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function viewInvoice(Receipt $invoice)
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin();

        if (!$isAdmin && $invoice->user_id !== $user->id) {
            abort(403);
        }
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

        $user = Auth::user();
        $isAdmin = $this->isAdmin();

        if (!$isAdmin && $invoice->user_id !== $user->id) {
            abort(403);
        }

        // Only prevent deletion of paid invoices for non-admin users
        if (!$isAdmin && $invoice->is_paid) {
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

    public function updateInvoiceStatus($invoiceId)
    {
        $invoice = Receipt::find($invoiceId);
        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        if (!$this->isAdmin()) {
            abort(403);
        }

        $invoice->update(['is_paid' => true, 'paid_date' => now()]);
        session()->flash('message', 'Invoice marked as paid successfully.');
        $this->updateStatusInvoiceId = null;
    }

    public function clearFilters()
    {
        $this->filterStatus = '';
        $this->filterDate = '';
        $this->searchName = '';
    }

    public function render()
    {
        $invoices = $this->getInvoices();
        $viewInvoice = $this->viewInvoiceId ? Receipt::find($this->viewInvoiceId) : null;
        
        // Calculate this month's paid total
        $now = now();
        $thisMonthPaidTotal = Receipt::whereBetween('paid_date', [
            $now->startOfMonth(),
            $now->endOfMonth()
        ])->where('is_paid', true)->sum('amount');

        return view('livewire.invoices', [
            'invoices' => $invoices,
            'viewInvoice' => $viewInvoice,
            'isAdmin' => $this->isAdmin(),
            'thisMonthPaidTotal' => $thisMonthPaidTotal,
        ]);
    }
}
