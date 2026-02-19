<?php

namespace App\Livewire\Admin;

use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserEdit extends Component
{
    public User $user;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $iban = '';
    public string $phoneNumber = '';
    public ?int $roleId = null;
    public ?int $countryId = null;

    public bool $showDeleteModal = false;
    public string $deletePassword = '';
    public string $deletePasswordError = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->iban = $user->iban ?? '';
        $this->phoneNumber = $user->phoneNumber ?? '';
        $this->roleId = $user->roleId;
        $this->countryId = $user->countryId;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->userId, 'userId')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'iban' => ['required', 'string', 'max:255'],
            'phoneNumber' => ['required', 'string', 'max:255'],
            'roleId' => ['nullable', 'exists:roles,roleId'],
            'countryId' => ['nullable', 'exists:countries,countryId'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        unset($validated['password_confirmation']);

        $this->user->update($validated);

        session()->flash('message', __('User updated successfully.'));

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function confirmDelete(): void
    {
        $this->deletePassword = '';
        $this->deletePasswordError = '';
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if (! Hash::check($this->deletePassword, Auth::user()->password)) {
            $this->deletePasswordError = __('Incorrect password.');
            return;
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && $this->user->roleId === $adminRole->roleId) {
            $adminCount = User::where('roleId', $adminRole->roleId)->count();
            if ($adminCount <= 1) {
                $this->deletePasswordError = __('Cannot delete the last admin user.');
                return;
            }
        }

        $this->user->delete();

        session()->flash('message', __('User deleted successfully.'));
        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deletePassword = '';
        $this->deletePasswordError = '';
    }

    public function render()
    {
        return view('livewire.admin.user-edit', [
            'roles' => Role::all(),
            'countries' => Country::all(),
        ]);
    }
}

