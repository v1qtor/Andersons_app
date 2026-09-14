<?php

namespace Database\Seeders;

use App\Models\Allergy;
use App\Models\Checkpoint;
use App\Models\Location;
use App\Models\MealGuest;
use App\Models\NotificationType;
use App\Models\PlannedMeal;
use App\Models\Task;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tier 1: Independent lookup tables (no FK dependencies)
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            AllergySeeder::class,
            MealSeeder::class,
            TaskCategorySeeder::class,
            TaskPrioritySeeder::class,
            LocationSeeder::class,
            RepeatabilityTypeSeeder::class,
            NotificationTypeSeeder::class,
            TripCategorySeeder::class,
            StatusSeeder::class,
            TripStatusSeeder::class,
            AttachedFileSeeder::class,
            FolderSeeder::class,
            NotificationSeeder::class,
        ]);

        // Tier 2: Tables depending on Tier 1
        $this->call([
            UserSeeder::class,
            CheckpointSeeder::class,
            RecurringTaskSeeder::class,
        ]);

        // Tier 3: Tables depending on Tier 2
        $this->call([
            UnavailabilityPeriodSeeder::class,
            ReceiptSeeder::class,
            PlannedMealSeeder::class,
            TaskSeeder::class,
            TripSeeder::class,
            PreferenceSeeder::class,
            TestInvoicesSeeder::class,
        ]);

        // Tier 4: Pivot / associative tables
        $users = User::all();
        $allergies = Allergy::all();
        $locations = Location::all();
        $notificationTypes = NotificationType::all();
        $plannedMeals = PlannedMeal::all();
        $tasks = Task::all();
        $trips = Trip::all();
        $checkpoints = Checkpoint::all();

        // UserAllergy
        $users->each(function ($user) use ($allergies) {
            $user->allergies()->attach(
                $allergies->random(rand(0, 3))->pluck('id')->toArray()
            );
        });

        // MealSubscription + MealGuests
        $plannedMeals->each(function ($plannedMeal) use ($users) {
            $selectedUsers = $users->random(rand(1, 4));
            foreach ($selectedUsers as $user) {
                $confirmed = fake()->boolean(70);
                $plannedMeal->subscribers()->attach($user->id, [
                    'confirmed' => $confirmed,
                ]);

                // Only confirmed subscribers can bring guests; give some of them 1–2.
                if ($confirmed && fake()->boolean(30)) {
                    MealGuest::factory()->count(rand(1, 2))->create([
                        'planned_meal_id'    => $plannedMeal->id,
                        'invited_by_user_id' => $user->id,
                    ]);
                }
            }
        });

        // TaskLocation
        $tasks->each(function ($task) use ($locations) {
            $task->locations()->attach(
                $locations->random(rand(1, 2))->pluck('id')->toArray()
            );
        });

        // NotificationSettings
        $users->each(function ($user) use ($notificationTypes) {
            foreach ($notificationTypes as $type) {
                $user->notificationSettings()->attach($type->id, [
                    'value' => fake()->boolean(),
                ]);
            }
        });

        // UserTrip
        $trips->each(function ($trip) use ($users) {
            $selectedUsers = $users->random(rand(2, 5));
            $first = true;
            foreach ($selectedUsers as $user) {
                $trip->users()->attach($user->id, [
                    'is_organizer' => $first,
                ]);
                $first = false;
            }
        });

        // TripCheckpoint
        $trips->each(function ($trip) use ($checkpoints) {
            $selectedCheckpoints = $checkpoints->random(rand(2, 4));
            $order = 1;
            foreach ($selectedCheckpoints as $checkpoint) {
                $trip->checkpoints()->attach($checkpoint->id, [
                    'arrival_date' => fake()->dateTimeBetween($trip->start_date, $trip->end_date),
                    'is_confirmed' => fake()->boolean(),
                    'order' => $order++,
                ]);
            }
        });
    }
}
