<?php

namespace App\Livewire\Admin;

use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserCreate extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $iban = '';
    public string $phoneNumber = '';
    public ?int $roleId = null;
    public ?int $countryId = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'iban' => ['required', 'string', 'max:255'],
            'phoneNumber' => ['required', 'string', 'max:255'],
            'roleId' => ['nullable', 'exists:roles,roleId'],
            'countryId' => ['nullable', 'exists:countries,countryId'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        session()->flash('message', __('User created successfully.'));

        $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.user-create', [
            'roles' => Role::all(),
            'countries' => Country::all(),
        ]);
    }
}

