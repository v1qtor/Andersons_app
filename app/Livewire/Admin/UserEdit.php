<?php

namespace App\Livewire\Admin;

use App\Models\Birthdate;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UserEdit extends Component
{
    public User $user;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $iban = '';
    public string $phone_number = '';
    public ?int $role_id = null;
    public string $birthdate = '';

    public bool $showDeleteModal = false;
    public string $deletePassword = '';
    public string $deletePasswordError = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->iban = $user->iban ?? '';
        $this->phone_number = $user->phone_number ?? '';
        $this->role_id = $user->role_id;
        $this->birthdate = $user->birthdate ? $user->birthdate->format('Y-m-d') : '';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'iban' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'birthdate' => ['nullable', 'date'],
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

        // Sync birthdate → birthdates table (is_user = true)
        $birthdate = $validated['birthdate'] ?? null;
        if ($birthdate) {
            Birthdate::updateOrCreate(
                ['user_id' => $this->user->id, 'is_user' => true],
                ['name' => $this->user->name, 'birthdate' => $birthdate, 'is_user' => true]
            );
        } else {
            Birthdate::where('user_id', $this->user->id)->where('is_user', true)->delete();
        }

        session()->flash('message', __('User updated successfully.'));

        $this->redirect(route('admin.panel'), navigate: true);
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
        if ($adminRole && $this->user->role_id === $adminRole->id) {
            $adminCount = User::where('role_id', $adminRole->id)->count();
            if ($adminCount <= 1) {
                $this->deletePasswordError = __('Cannot delete the last admin user.');
                return;
            }
        }

        $this->user->delete();

        session()->flash('message', __('User deleted successfully.'));
        $this->redirect(route('admin.panel'), navigate: true);
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
        ]);
    }
}

