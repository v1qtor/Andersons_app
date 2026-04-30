{{-- Reusable User Form Component - used in both create and edit --}}
@props([
    'isEdit' => false,
    'roles' => collect(),
])

<div class="space-y-6">
    {{-- Name & Email --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:input
            wire:model="name"
            :label="__('Full Name')"
            type="text"
            required
            placeholder="{{ __('Enter full name') }}"
        />

        <flux:input
            wire:model="email"
            :label="__('Email Address')"
            type="email"
            required
            placeholder="{{ __('Enter email address') }}"
        />
    </div>

    {{-- Phone Number & IBAN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:input
            wire:model="phone_number"
            :label="__('Phone Number')"
            type="tel"
            required
            placeholder="{{ __('Enter phone number') }}"
        />

        <flux:input
            wire:model="iban"
            :label="__('IBAN')"
            type="text"
            required
            placeholder="{{ __('Enter IBAN') }}"
        />
    </div>

    {{-- Role --}}
    <flux:select wire:model="role_id" :label="__('Role')" placeholder="{{ __('Select a role') }}">
        @foreach ($roles as $role)
            <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
        @endforeach
    </flux:select>

    {{-- Birthdate --}}
    <flux:input
        wire:model="birthdate"
        :label="__('Date of Birth')"
        type="date"
        placeholder="{{ __('YYYY-MM-DD') }}"
    />

    {{-- Password --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:input
            wire:model="password"
            :label="$isEdit ? __('New Password (leave blank to keep current)') : __('Password')"
            type="password"
            :required="!$isEdit"
            placeholder="{{ $isEdit ? __('Leave blank to keep current') : __('Enter password') }}"
        />

        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm Password')"
            type="password"
            :required="!$isEdit"
            placeholder="{{ __('Confirm password') }}"
        />
    </div>
</div>

