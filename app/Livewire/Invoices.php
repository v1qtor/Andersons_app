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
    public ?int $viewInvoiceId = null;
    public ?int $deleteInvoiceId = null;

    public function mount()
    {
        $user = Auth::user();
        $allowedRoles = ['Staff', 'Chef', 'Admin'];
        
        if (!$user || !$user->role || !in_array($user->role->name, $allowedRoles)) {
            abort(403, __('Unauthorized. Staff access required.'));
        }
    }

    public function getInvoices()
    {
        $query = Receipt::where('user_id', Auth::id())
            ->with('category')
            ->orderBy('created_at', 'desc');

        if ($this->filterStatus) {
            $query->where('is_paid', $this->filterStatus === 'paid');
        }

        if ($this->filterDate) {
            $query->whereDate('bill_date', $this->filterDate);
        }

        return $query->get();
    }

    public function viewInvoice(Receipt $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
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

        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }

        if ($invoice->is_paid) {
            session()->flash('error', 'Cannot delete a paid invoice.');
            $this->deleteInvoiceId = null;
            return;
        }

        $invoice->delete();
        session()->flash('message', 'Invoice deleted successfully.');
        $this->deleteInvoiceId = null;
    }

    public function render()
    {
        $invoices = $this->getInvoices();
        $viewInvoice = $this->viewInvoiceId ? Receipt::find($this->viewInvoiceId) : null;

        return view('livewire.invoices', [
            'invoices' => $invoices,
            'viewInvoice' => $viewInvoice,
        ]);
    }
}
