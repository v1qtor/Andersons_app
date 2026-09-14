<?php

namespace App\Livewire;

use App\Models\Allergy;
use App\Models\Birthdate;
use App\Models\Preference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Settings extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone_number = '';

    public string $address = '';

    public string $iban = '';

    public bool $showIban = false;

    public string $birthdate = '';

    public string $newAllergy = '';

    public string $newPreference = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public array $notifications = [
        'trips' => true,
        'taskAssignments' => true,
        'collaborationRequests' => true,
        'receiptApprovals' => true,
        'mealNotifications' => true,
    ];

    /**
     * Mount the component and load user data.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number ?? '';
        $this->address = $user->address ?? '';
        $this->iban = $user->iban ?? '';
        $this->birthdate = $user->birthdate ? $user->birthdate->format('Y-m-d') : '';

        // Load notification settings from database
        $notificationSettings = $user->notificationSettings()->get();

        // Initialize with defaults first
        $this->notifications = [
            'trips' => true,
            'taskAssignments' => true,
            'collaborationRequests' => true,
            'receiptApprovals' => true,
            'mealNotifications' => true,
        ];

        // Override with database values if they exist
        foreach ($notificationSettings as $setting) {
            $typeId = $setting->pivot->notification_type_id;
            $value = $setting->pivot->value;

            // Parse stored notification settings
            $category = match ($typeId) {
                1 => 'trips',
                2 => 'taskAssignments',
                5 => 'collaborationRequests',
                3 => 'receiptApprovals',
                4 => 'mealNotifications',
                default => null
            };

            if ($category && $value !== null) {
                try {
                    // Value is stored as a string ("true" or "false")
                    $this->notifications[$category] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } catch (\Exception $e) {
                    // Use default values if decode fails
                }
            }
        }
    }

    /**
     * Update personal information.
     */
    public function updatePersonalInfo(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.Auth::id(),
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'iban' => 'nullable|string|max:50',
            'birthdate' => 'nullable|date',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'iban' => $this->iban,
            'birthdate' => $this->birthdate ?: null,
        ]);

        // Sync users.birthdate → birthdates table (is_user = true)
        if ($this->birthdate) {
            Birthdate::updateOrCreate(
                ['user_id' => $user->id, 'is_user' => true],
                ['name' => $user->name, 'birthdate' => $this->birthdate, 'is_user' => true]
            );
        } else {
            // User cleared their birthdate — remove the synced record
            Birthdate::where('user_id', $user->id)->where('is_user', true)->delete();
        }

        session()->flash('status', 'Personal information updated successfully!');
    }

    /**
     * Toggle IBAN visibility.
     */
    public function toggleShowIban(): void
    {
        $this->showIban = ! $this->showIban;
    }

    /**
     * Toggle notification setting (popup only).
     */
    public function toggleNotification(string $category): void
    {
        $this->notifications[$category] = ! $this->notifications[$category];

        // Map notification settings to the database
        $user = Auth::user();

        // Get notification type ID based on category
        $notificationTypeId = match ($category) {
            'trips' => 1,
            'taskAssignments' => 2,
            'collaborationRequests' => 5,
            'receiptApprovals' => 3,
            'mealNotifications' => 4,
            default => null
        };

        if ($notificationTypeId) {
            $settingValue = $this->notifications[$category] ? 'true' : 'false';
            $user->notificationSettings()
                ->syncWithoutDetaching([
                    $notificationTypeId => ['value' => $settingValue],
                ]);
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(): void
    {
        $key = 'change-password:'.Auth::id();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            $this->dispatch('rate-limited', seconds: $seconds);
            $this->addError('currentPassword', "Too many attempts. Please wait {$seconds} second(s).");

            return;
        }

        RateLimiter::hit($key, 1);
        $this->dispatch('rate-limited', seconds: 1);

        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|same:newPasswordConfirmation',
        ], [
            'newPassword.same' => 'The new password confirmation does not match.',
        ]);

        $user = Auth::user();

        if (! Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');

            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';

        $this->dispatch('password-updated');
        $this->dispatch('toast', message: 'Password changed successfully!', type: 'success');
    }

    public function addAllergy(): void
    {
        $this->validate(['newAllergy' => 'required|string|max:100']);

        $user = Auth::user();
        $allergy = Allergy::firstOrCreate(['name' => trim($this->newAllergy)]);

        if ($user->allergies()->where('allergies.id', $allergy->id)->exists()) {
            $this->dispatch('toast', message: "'".$allergy->name."' is already in your allergies list.", type: 'error');
        } else {
            $user->allergies()->attach($allergy->id);
            $this->dispatch('toast', message: "'".$allergy->name."' has been added to your allergies.", type: 'success');
        }

        $this->newAllergy = '';
    }

    public function removeAllergy(int $allergyId): void
    {
        $allergy = Allergy::find($allergyId);
        Auth::user()->allergies()->detach($allergyId);
        $this->dispatch('toast', message: "'".($allergy?->name ?? 'Allergy')."' has been removed from your allergies.", type: 'error');
    }

    public function addPreference(): void
    {
        $this->validate(['newPreference' => 'required|string|max:100']);

        $user = Auth::user();
        $name = trim($this->newPreference);

        if ($user->preferences()->where('name', $name)->exists()) {
            $this->dispatch('toast', message: "'".$name."' is already in your food preferences.", type: 'error');
        } else {
            $user->preferences()->create(['name' => $name]);
            $this->dispatch('toast', message: "'".$name."' has been added to your food preferences.", type: 'success');
        }

        $this->newPreference = '';
    }

    public function removePreference(int $preferenceId): void
    {
        $preference = Preference::find($preferenceId);
        Auth::user()->preferences()->where('id', $preferenceId)->delete();
        $this->dispatch('toast', message: "'".($preference?->name ?? 'Preference')."' has been removed from your food preferences.", type: 'error');
    }

    public function render()
    {
        return view('livewire.settings', [
            'userAllergies' => Auth::user()->allergies()->get(),
            'userPreferences' => Auth::user()->preferences()->get(),
        ]);
    }
}
