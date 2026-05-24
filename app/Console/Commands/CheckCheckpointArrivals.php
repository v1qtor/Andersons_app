<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckCheckpointArrivals extends Command
{
    protected $signature = 'checkpoints:check-arrivals';
    protected $description = 'Check for checkpoints where one person hasn\'t arrived after 2 hours';

    public function handle()
    {
        $twoHoursAgo = Carbon::now()->subHours(2);

        // Get all trips that are currently active
        $trips = Trip::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with(['checkpoints', 'users'])
            ->get();

        foreach ($trips as $trip) {
            foreach ($trip->checkpoints as $checkpoint) {
                // Count confirmed users for this checkpoint
                $confirmedCount = $trip->checkpoints()
                    ->where('id', $checkpoint->id)
                    ->wherePivot('is_confirmed', true)
                    ->wherePivot('arrival_date', '!=', null)
                    ->wherePivot('arrival_date', '<=', $twoHoursAgo)
                    ->count();

                $totalUsers = $trip->users()->count();

                // If someone is still unconfirmed after 2 hours
                if ($confirmedCount > 0 && $confirmedCount < $totalUsers) {
                    $unconfirmedUsers = $trip->users()
                        ->whereNotIn('id', $trip->checkpoints()
                            ->where('id', $checkpoint->id)
                            ->wherePivot('is_confirmed', true)
                            ->pluck('user_id'))
                        ->get();

                    foreach ($unconfirmedUsers as $user) {
                        $user->notifications()->create([
                            'title' => 'Checkpoint Check-in Alert',
                            'message' => 'Everyone has reached "' . $checkpoint->location . '" except you. Please confirm when you arrive.',
                            'type' => 'warning',
                            'trip_id' => $trip->id,
                            'action_url' => route('trips.index'),
                        ]);
                    }
                }
            }
        }

        $this->info('Checkpoint arrival check completed.');
    }
}
