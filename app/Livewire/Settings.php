<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Settings extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phoneNumber = '';
    public string $iban = '';
    public string $bankCountry = 'United Kingdom';

    public array $notifications = [
        'tripDelayAlerts' => ['email' => true, 'popup' => true],
        'taskReminders' => ['email' => false, 'popup' => true],
        'receiptApprovals' => ['email' => true, 'popup' => false],
        'dinnerSignups' => ['email' => true, 'popup' => true],
    ];

    /**
     * Mount the component and load user data.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phoneNumber = $user->phoneNumber ?? '';
        $this->iban = $user->iban ?? '';

        // Load notification settings from database
        $notificationSettings = $user->notificationSettings()->get();
        foreach ($notificationSettings as $setting) {
            $typeId = $setting->pivot->notificationTypeId;
            $value = $setting->pivot->value;

            // Parse stored notification settings
            $category = match ($typeId) {
                1 => 'tripDelayAlerts',
                2 => 'taskReminders',
                3 => 'receiptApprovals',
                4 => 'dinnerSignups',
                default => null
            };

            if ($category && $value) {
                try {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $this->notifications[$category] = $decoded;
                    }
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
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id() . ',userId',
            'phoneNumber' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:50',
            'bankCountry' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'iban' => $this->iban,
        ]);

        session()->flash('status', 'Personal information updated successfully!');
    }

    /**
     * Toggle notification setting.
     */
    public function toggleNotification(string $category, string $type): void
    {
        $this->notifications[$category][$type] = !$this->notifications[$category][$type];

        // Map notification settings to the database
        $user = Auth::user();

        // Get notification type ID based on category
        $notificationTypeId = match ($category) {
            'tripDelayAlerts' => 1,
            'taskReminders' => 2,
            'receiptApprovals' => 3,
            'dinnerSignups' => 4,
            default => null
        };

        if ($notificationTypeId) {
            $settingValue = json_encode($this->notifications[$category]);
            $user->notificationSettings()
                ->syncWithoutDetaching([
                    $notificationTypeId => ['value' => $settingValue]
                ]);
        }

        session()->flash('message', 'Notification preferences updated!');
    }

    public function render()
    {
        return view('livewire.settings');
    }
}
