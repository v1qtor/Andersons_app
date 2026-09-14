<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    private array $taskTemplates = [
        // Cleaning
        ['title' => 'Vacuum the living room',    'description' => 'Thorough vacuum including under furniture and along skirting boards.', 'category' => 'Cleaning', 'duration' => 60],
        ['title' => 'Clean the bathrooms',        'description' => 'Scrub toilets, sinks, and showers. Restock toiletries.',              'category' => 'Cleaning', 'duration' => 90],
        ['title' => 'Mop the kitchen floor',      'description' => 'Sweep and mop all kitchen and pantry floor areas.',                   'category' => 'Cleaning', 'duration' => 45],
        ['title' => 'Dust the bedrooms',          'description' => 'Dust all surfaces, mirrors, and light fittings in the bedrooms.',     'category' => 'Cleaning', 'duration' => 60],
        ['title' => 'Clean the windows',          'description' => 'Clean inside and outside of ground-floor windows.',                   'category' => 'Cleaning', 'duration' => 120],
        ['title' => 'Deep clean the oven',        'description' => 'Remove racks, soak and scrub the oven interior.',                     'category' => 'Cleaning', 'duration' => 90],
        ['title' => 'Polish the silverware',      'description' => 'Polish all silverware and return to the dining room cabinet.',         'category' => 'Cleaning', 'duration' => 60],
        ['title' => 'Clean the conservatory',     'description' => 'Wipe glass panels, sweep floor, and tidy furniture.',                 'category' => 'Cleaning', 'duration' => 75],
        // Maintenance
        ['title' => 'Check the boiler pressure',  'description' => 'Inspect boiler, top up pressure if needed and bleed radiators.',      'category' => 'Maintenance', 'duration' => 45],
        ['title' => 'Replace light bulbs',        'description' => 'Replace any blown bulbs throughout the house and outbuildings.',       'category' => 'Maintenance', 'duration' => 30],
        ['title' => 'Oil door hinges',            'description' => 'Oil all squeaky door hinges and check door handles.',                  'category' => 'Maintenance', 'duration' => 30],
        ['title' => 'Test smoke alarms',          'description' => 'Test all smoke and carbon monoxide detectors, replace batteries.',     'category' => 'Maintenance', 'duration' => 45],
        ['title' => 'Inspect the roof gutters',   'description' => 'Check gutters for blockages and clear out leaves and debris.',         'category' => 'Maintenance', 'duration' => 90],
        ['title' => 'Service the heating system',  'description' => 'Run the seasonal check on the central heating and thermostats.',      'category' => 'Maintenance', 'duration' => 120],
        ['title' => 'Bleed the radiators',        'description' => 'Bleed all radiators to remove trapped air and improve heat output.',   'category' => 'Maintenance', 'duration' => 60],
        ['title' => 'Fix the garden gate latch',  'description' => 'Repair or replace the latch on the back garden gate.',                'category' => 'Maintenance', 'duration' => 45],
        // Gardening
        ['title' => 'Mow the front lawn',         'description' => 'Mow and edge the front lawn, collect clippings.',                     'category' => 'Gardening', 'duration' => 90],
        ['title' => 'Trim the hedges',            'description' => 'Trim and shape the boundary hedges along the driveway.',               'category' => 'Gardening', 'duration' => 120],
        ['title' => 'Weed the flower beds',       'description' => 'Remove weeds from all flower beds and borders.',                       'category' => 'Gardening', 'duration' => 90],
        ['title' => 'Plant seasonal bulbs',       'description' => 'Plant new bulbs in the front garden borders.',                         'category' => 'Gardening', 'duration' => 120],
        ['title' => 'Water the greenhouse',       'description' => 'Water all greenhouse plants and check for pests.',                     'category' => 'Gardening', 'duration' => 45],
        ['title' => 'Rake and compost leaves',    'description' => 'Rake fallen leaves and add to the compost heap.',                      'category' => 'Gardening', 'duration' => 75],
        ['title' => 'Prune the rose bushes',      'description' => 'Prune and deadhead the rose garden.',                                  'category' => 'Gardening', 'duration' => 60],
        ['title' => 'Treat the patio',            'description' => 'Pressure-wash and treat the back patio slabs.',                        'category' => 'Gardening', 'duration' => 150],
        // Cooking
        ['title' => 'Prepare Sunday dinner',      'description' => 'Cook a full roast dinner for the family.',                             'category' => 'Cooking', 'duration' => 180],
        ['title' => 'Weekly meal prep',           'description' => 'Batch-cook meals for the week and portion into containers.',            'category' => 'Cooking', 'duration' => 150],
        ['title' => 'Bake fresh bread',           'description' => 'Prepare and bake sourdough loaves for the week.',                      'category' => 'Cooking', 'duration' => 120],
        ['title' => 'Stock the pantry shelves',   'description' => 'Rotate tinned goods and check expiry dates in the pantry.',            'category' => 'Cooking', 'duration' => 45],
        ['title' => 'Prepare packed lunches',     'description' => 'Make packed lunches for the following day.',                            'category' => 'Cooking', 'duration' => 30],
        ['title' => 'Plan the weekly menus',      'description' => 'Draft the meal plan for the coming week.',                             'category' => 'Cooking', 'duration' => 30],
        ['title' => 'Prepare afternoon tea',      'description' => 'Set up afternoon tea with scones and sandwiches.',                     'category' => 'Cooking', 'duration' => 60],
        ['title' => 'Deep clean the fridge',      'description' => 'Empty, clean, and reorganise the fridge contents.',                    'category' => 'Cooking', 'duration' => 60],
        // Shopping
        ['title' => 'Weekly grocery shop',        'description' => 'Buy fresh fruit, veg, dairy, and bread from the local shops.',         'category' => 'Shopping', 'duration' => 90],
        ['title' => 'Pick up cleaning supplies',  'description' => 'Restock cleaning products from the hardware store.',                   'category' => 'Shopping', 'duration' => 60],
        ['title' => 'Garden centre trip',         'description' => 'Purchase compost, plants, and garden tools.',                           'category' => 'Shopping', 'duration' => 90],
        ['title' => 'Collect the dry cleaning',   'description' => 'Pick up dry cleaning from the High Street.',                           'category' => 'Shopping', 'duration' => 30],
        ['title' => 'Visit the farmers market',   'description' => 'Pick up free-range eggs, local honey, and seasonal produce.',          'category' => 'Shopping', 'duration' => 75],
        ['title' => 'Pharmacy run',               'description' => 'Collect prescriptions and top up the first-aid kit.',                  'category' => 'Shopping', 'duration' => 30],
        ['title' => 'Order office supplies',      'description' => 'Place an order for printer ink, paper, and stationery.',               'category' => 'Shopping', 'duration' => 20],
        ['title' => 'Buy pet supplies',           'description' => 'Restock pet food and grooming products.',                              'category' => 'Shopping', 'duration' => 45],
        // Laundry
        ['title' => 'Wash the bed linen',         'description' => 'Strip and wash all bed linen, pillowcases, and duvet covers.',         'category' => 'Laundry', 'duration' => 60],
        ['title' => 'Iron the shirts',            'description' => 'Iron and press shirts and formal clothing for the week.',              'category' => 'Laundry', 'duration' => 45],
        ['title' => 'Dry cleaning drop-off',      'description' => 'Drop off suits and delicate items at the dry cleaner.',                'category' => 'Laundry', 'duration' => 20],
        ['title' => 'Wash the towels',            'description' => 'Launder all bath and kitchen towels on a hot wash.',                   'category' => 'Laundry', 'duration' => 45],
        ['title' => 'Fold and sort laundry',      'description' => 'Fold dried laundry and sort into wardrobes.',                          'category' => 'Laundry', 'duration' => 40],
        ['title' => 'Clean the curtains',         'description' => 'Take down and wash or steam-clean the curtains.',                      'category' => 'Laundry', 'duration' => 120],
        ['title' => 'Wash the table linens',      'description' => 'Launder tablecloths and napkins for the dining room.',                 'category' => 'Laundry', 'duration' => 45],
        ['title' => 'Sort the donation pile',     'description' => 'Sort through old clothes and bag items for charity.',                  'category' => 'Laundry', 'duration' => 60],
        // Repairs
        ['title' => 'Fix the leaky tap',          'description' => 'Replace the washer on the kitchen tap to stop the drip.',              'category' => 'Repairs', 'duration' => 45],
        ['title' => 'Patch the wall plaster',     'description' => 'Fill and sand the cracks in the hallway wall.',                        'category' => 'Repairs', 'duration' => 90],
        ['title' => 'Repair the shed door',       'description' => 'Realign and rehang the garden shed door.',                             'category' => 'Repairs', 'duration' => 60],
        ['title' => 'Fix the fence panel',        'description' => 'Replace the broken fence panel on the east boundary.',                 'category' => 'Repairs', 'duration' => 120],
        ['title' => 'Repair the towel rail',      'description' => 'Reattach the towel rail in the main bathroom.',                        'category' => 'Repairs', 'duration' => 30],
        ['title' => 'Fix the squeaky stair',      'description' => 'Secure the loose tread on the third stair.',                           'category' => 'Repairs', 'duration' => 45],
        ['title' => 'Mend the window latch',      'description' => 'Repair the stiff window latch in the guest bedroom.',                  'category' => 'Repairs', 'duration' => 30],
        ['title' => 'Repair the gate post',       'description' => 'Reset and secure the leaning front gate post.',                        'category' => 'Repairs', 'duration' => 120],
        // Organization
        ['title' => 'File household paperwork',   'description' => 'Sort and file bills, receipts, and correspondence.',                   'category' => 'Organization', 'duration' => 60],
        ['title' => 'Organise the storage room',  'description' => 'Tidy and label boxes in the storage room.',                            'category' => 'Organization', 'duration' => 120],
        ['title' => 'Sort the recycling',         'description' => 'Separate recyclables and take to the collection point.',               'category' => 'Organization', 'duration' => 30],
        ['title' => 'Update the inventory list',  'description' => 'Review and update the household inventory spreadsheet.',               'category' => 'Organization', 'duration' => 60],
        ['title' => 'Organise the tool shed',     'description' => 'Clean, sort, and hang tools in the garden shed.',                      'category' => 'Organization', 'duration' => 90],
        ['title' => 'Clear the garage',           'description' => 'Sort through the garage and dispose of unwanted items.',               'category' => 'Organization', 'duration' => 150],
        ['title' => 'Label the freezer items',    'description' => 'Check dates and label all items in the chest freezer.',                'category' => 'Organization', 'duration' => 30],
        ['title' => 'Archive old documents',      'description' => 'Shred or archive outdated paperwork from the filing cabinet.',         'category' => 'Organization', 'duration' => 60],
    ];

    public function run(): void
    {
        $users = User::all();
        $categories = TaskCategory::all();
        $priorities = TaskPriority::all();

        if ($users->isEmpty() || $categories->isEmpty() || $priorities->isEmpty()) {
            return;
        }

        $now = Carbon::now();
        $weekCount = 22; // ~5 months

        for ($week = 0; $week < $weekCount; $week++) {
            $weekStart = $now->copy()->startOfWeek()->addWeeks($week);

            // Create 10-14 tasks per week
            $taskCount = rand(10, 14);
            $weekTasks = [];
            $taskAssignments = []; // task index => [user_ids]

            for ($t = 0; $t < $taskCount; $t++) {
                $template = $this->taskTemplates[array_rand($this->taskTemplates)];
                $category = $categories->firstWhere('name', $template['category']) ?? $categories->random();

                // Random weekday (Mon-Sat)
                $dayOffset = rand(0, 5);
                $taskDate = $weekStart->copy()->addDays($dayOffset);

                // Realistic start time: 7:00–17:00
                $startHour = rand(7, 17);
                $startMinute = [0, 15, 30, 45][array_rand([0, 15, 30, 45])];
                $durationMins = $template['duration'];

                $taskStart = $taskDate->copy()->setTime($startHour, $startMinute);
                $taskEnd = $taskStart->copy()->addMinutes($durationMins);

                // Cap end time at 19:00
                if ($taskEnd->hour >= 19) {
                    $taskEnd->setTime(19, 0);
                }

                $isPast = $taskStart->lt($now);

                $task = Task::create([
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'start_date' => $taskStart,
                    'end_date' => $taskEnd,
                    'date' => $taskDate->copy()->startOfDay(),
                    'task_category_id' => $category->id,
                    'task_priority_id' => $priorities->random()->id,
                    'is_complete' => $isPast ? (rand(1, 100) <= 85) : false,
                    'recurring_task_id' => null,
                ]);

                $weekTasks[$t] = $task;
                $taskAssignments[$t] = [];
            }

            // Assign each user to 2-3 tasks this week, avoiding duplicate pivots
            foreach ($users as $user) {
                $numAssign = rand(2, 3);
                $indices = range(0, count($weekTasks) - 1);
                shuffle($indices);

                $assigned = 0;
                foreach ($indices as $idx) {
                    if ($assigned >= $numAssign) {
                        break;
                    }
                    if (! in_array($user->id, $taskAssignments[$idx])) {
                        $isOwner = empty($taskAssignments[$idx]);
                        $weekTasks[$idx]->users()->attach($user->id, [
                            'is_owner' => $isOwner,
                        ]);
                        $taskAssignments[$idx][] = $user->id;
                        $assigned++;
                    }
                }
            }
        }
    }
}
