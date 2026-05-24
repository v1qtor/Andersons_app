<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckTripOverdue extends Command
{
    protected $signature = 'trips:check-overdue';
    protected $description = 'Send notifications when a trip is overdue (buffer alert passed)';

    public function handle()
    {
        $now = now();

        $overdueTrips = Trip::where('buffer_alert', '<', $now)
            ->where('status_id', '!=', function ($query) {
                $query->selectRaw('id')
                    ->from('statuses')
                    ->where('name', 'completed')
                    ->where('type', 'trip');
            })
            ->with('users')
            ->get();

        foreach ($overdueTrips as $trip) {
            // Notify all household members
            foreach ($trip->users as $user) {
                $user->notifications()->create([
                    'title' => 'Trip Overdue Alert: ' . $trip->name,
                    'message' => 'The trip "' . $trip->name . '" has not returned by the expected time. Please check if everyone is accounted for.',
                    'type' => 'error',
                    'trip_id' => $trip->id,
                    'action_url' => route('trips.index'),
                ]);
            }
        }

        $this->info('Trip overdue check completed.');
    }
}
