<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showConfirmModal = false;
    public string $pendingAction = '';
    public int $pendingUserId = 0;
    public string $confirmPassword = '';
    public string $passwordError = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function prepareAction(string $action, int $userId): void
    {
        $this->pendingAction = $action;
        $this->pendingUserId = $userId;
        $this->confirmPassword = '';
        $this->passwordError = '';
        $this->showConfirmModal = true;
    }

    public function executeAction(): void
    {
        if (! Hash::check($this->confirmPassword, Auth::user()->password)) {
            $this->passwordError = __('Incorrect password.');
            return;
        }

        match ($this->pendingAction) {
            'delete' => $this->deleteUser($this->pendingUserId),
            'toggle' => $this->toggleActive($this->pendingUserId),
            'edit'   => $this->redirectToEdit($this->pendingUserId),
        };

        $this->cancelAction();
    }

    public function cancelAction(): void
    {
        $this->showConfirmModal = false;
        $this->pendingAction = '';
        $this->pendingUserId = 0;
        $this->confirmPassword = '';
        $this->passwordError = '';
    }

    private function redirectToEdit(int $userId): void
    {
        $this->redirect(route('admin.users.edit', $userId), navigate: true);
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && $user->roleId === $adminRole->roleId) {
            $adminCount = User::where('roleId', $adminRole->roleId)->count();
            if ($adminCount <= 1) {
                session()->flash('error', __('Cannot delete the last admin user.'));
                return;
            }
        }

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

