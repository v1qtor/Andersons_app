<?php

namespace Database\Seeders;

use App\Models\Allergy;
use App\Models\AttachedFile;
use App\Models\Category;
use App\Models\Checkpoint;
use App\Models\Country;
use App\Models\Folder;
use App\Models\Location;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\PlannedMeal;
use App\Models\Preference;
use App\Models\Receipt;
use App\Models\RecurringTask;
use App\Models\RepeatabilityType;
use App\Models\Role;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\Trip;
use App\Models\TripCategory;
use App\Models\UnavailabilityPeriod;
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
        $roles = Role::factory(5)->create();
        $countries = Country::factory(5)->create();
        $categories = Category::factory(5)->create();
        $allergies = Allergy::factory(8)->create();
        $meals = Meal::factory(8)->create();
        $taskCategories = TaskCategory::factory(5)->create();
        $taskPriorities = TaskPriority::factory(4)->create();
        $locations = Location::factory(7)->create();
        $repeatabilityTypes = RepeatabilityType::factory(5)->create();
        $notificationTypes = NotificationType::factory(5)->create();
        $tripCategories = TripCategory::factory(5)->create();
        $statuses = Status::factory(5)->create();
        $attachedFiles = AttachedFile::factory(5)->create();
        $folders = Folder::factory(5)->create();
        $notifications = Notification::factory(8)->create();

        // Tier 2: Tables depending on Tier 1
        $users = User::factory(8)->create();
        $checkpoints = Checkpoint::factory(8)->create();
        $recurringTasks = RecurringTask::factory(5)->create();

        // Tier 3: Tables depending on Tier 2
        UnavailabilityPeriod::factory(7)->create();
        Receipt::factory(10)->create();
        $plannedMeals = PlannedMeal::factory(8)->create();
        $tasks = Task::factory(10)->create();
        $trips = Trip::factory(6)->create();
        Preference::factory(10)->create();

        // Tier 4: Pivot / associative tables
        // UserAllergy
        $users->each(function ($user) use ($allergies) {
            $user->allergies()->attach(
                $allergies->random(rand(0, 3))->pluck('allergyId')->toArray()
            );
        });

        // MealSubscription
        $plannedMeals->each(function ($plannedMeal) use ($users) {
            $selectedUsers = $users->random(rand(1, 4));
            foreach ($selectedUsers as $user) {
                $plannedMeal->subscribers()->attach($user->userId, [
                    'guestName' => fake()->optional(0.3)->name(),
                ]);
            }
        });

        // UserTask
        $tasks->each(function ($task) use ($users) {
            $selectedUsers = $users->random(rand(1, 3));
            $first = true;
            foreach ($selectedUsers as $user) {
                $task->users()->attach($user->userId, [
                    'isOwner' => $first,
                ]);
                $first = false;
            }
        });

        // TaskLocation
        $tasks->each(function ($task) use ($locations) {
            $task->locations()->attach(
                $locations->random(rand(1, 2))->pluck('locationId')->toArray()
            );
        });

        // NotificationSettings
        $users->each(function ($user) use ($notificationTypes) {
            foreach ($notificationTypes as $type) {
                $user->notificationSettings()->attach($type->notificationTypeId, [
                    'value' => fake()->boolean(),
                ]);
            }
        });

        // UserTrip
        $trips->each(function ($trip) use ($users) {
            $selectedUsers = $users->random(rand(2, 5));
            $first = true;
            foreach ($selectedUsers as $user) {
                $trip->users()->attach($user->userId, [
                    'isOrganizer' => $first,
                ]);
                $first = false;
            }
        });

        // TripCheckpoint
        $trips->each(function ($trip) use ($checkpoints) {
            $selectedCheckpoints = $checkpoints->random(rand(2, 4));
            $order = 1;
            foreach ($selectedCheckpoints as $checkpoint) {
                $trip->checkpoints()->attach($checkpoint->checkpointId, [
                    'arrivalDate' => fake()->dateTimeBetween($trip->startDate, $trip->endDate),
                    'isConfirmed' => fake()->boolean(),
                    'order' => $order++,
                ]);
            }
        });
    }
}
