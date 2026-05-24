<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\UnavailabilityPeriod;
use App\Models\User;
use Carbon\Carbon;

class StaffShortageBanner extends Component
{
    public int $count = 0;
    public array $names = [];
    public bool $visible = true;

    public function mount()
    {
        if (auth()->guest() || auth()->user()->role?->name !== 'Admin') {
            abort(403);
        }

        $this->loadData();

        $key = 'staff_shortage_banner_dismissed_'.now()->format('Ymd');
        if (session()->get($key, false)) {
            $this->visible = false;
        }
    }

    public function loadData(): void
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        $userIds = UnavailabilityPeriod::where('start_date', '<=', $todayEnd)
            ->where('end_date', '>=', $todayStart)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();

        // Only consider users who have the Staff role
        $staffNames = User::whereIn('id', $userIds)
            ->whereHas('role', fn($q) => $q->where('name', 'Staff'))
            ->pluck('name')
            ->toArray();

        $this->count = count($staffNames);
        $this->names = $staffNames;
    }

    public function dismiss()
    {
        $key = 'staff_shortage_banner_dismissed_'.now()->format('Ymd');
        session()->put($key, true);
        $this->visible = false;
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.admin.staff-shortage-banner');
    }
}
