{{-- Reusable User Form Component - used in both create and edit --}}
@props([
    'isEdit' => false,
    'roles' => collect(),
    'countries' => collect(),
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

    {{-- Role & Country --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:select wire:model="role_id" :label="__('Role')" placeholder="{{ __('Select a role') }}">
            @foreach ($roles as $role)
                <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model="country_id" :label="__('Country')" placeholder="{{ __('Select a country') }}">
            @foreach ($countries as $country)
                <flux:select.option value="{{ $country->id }}">{{ $country->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

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

