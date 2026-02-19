<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->delete();

        session()->flash('message', __('User deleted successfully.'));
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['isActive' => ! $user->isActive]);

        $message = $user->isActive ? __('User activated successfully.') : __('User deactivated successfully.');
        session()->flash('message', $message);
    }

    public function render()
    {
        $users = User::with(['role', 'country'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.user-index', [
            'users' => $users,
        ]);
    }
}

