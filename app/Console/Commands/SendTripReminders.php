<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendTripReminders extends Command
{
    protected $signature = 'trips:send-reminders';

    protected $description = 'Send notifications for trips starting tomorrow';

    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->startOfDay();
        $tomorrowEnd = Carbon::tomorrow()->endOfDay();

        $trips = Trip::whereBetween('start_date', [$tomorrow, $tomorrowEnd])
            ->with('users')
            ->get();

        foreach ($trips as $trip) {
            foreach ($trip->users as $user) {
                // Create notification
                $user->notifications()->create([
                    'title' => 'Trip Reminder: '.$trip->name,
                    'message' => 'Your trip "'.$trip->name.'" is starting tomorrow!',
                    'type' => 'info',
                    'trip_id' => $trip->id,
                    'action_url' => route('trips.index'),
                ]);
            }
        }

        $this->info('Trip reminders sent successfully.');
    }
}
