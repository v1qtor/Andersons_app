<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\TripCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        $tripCategories = TripCategory::all();

        if ($tripCategories->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        $trips = [
            ['name' => 'Supply Run to Edinburgh',              'description' => 'Pick up bulk kitchen supplies and specialty ingredients from the wholesale supplier in Leith.', 'category' => 'Business',          'days' => 1, 'weeks' => -2, 'notes' => 'Bring the van — large order expected.'],
            ['name' => 'Antique Fair in Harrogate',            'description' => 'Browse the monthly antique fair at the convention centre for furniture and decor.',              'category' => 'Leisure',           'days' => 1, 'weeks' => -1, 'notes' => 'Cash preferred by most stallholders.'],
            ['name' => 'Family Weekend in the Lake District',  'description' => 'Weekend getaway to Windermere with a cottage stay and fell walking.',                           'category' => 'Family Vacation',   'days' => 3, 'weeks' => 2,  'notes' => 'Cottage booked under Mr. Anderson.'],
            ['name' => 'London Theatre Trip',                  'description' => 'Two-night stay in London to see a show in the West End and visit the galleries.',               'category' => 'Cultural',          'days' => 2, 'weeks' => 4,  'notes' => 'Tickets collected at the box office.'],
            ['name' => 'Wine Tasting in the Cotswolds',        'description' => 'Day trip to a vineyard near Cirencester for a private wine tasting and lunch.',                 'category' => 'Leisure',           'days' => 1, 'weeks' => 5,  'notes' => 'Designated driver needed.'],
            ['name' => 'Garden Show at Hampton Court',         'description' => 'Annual garden show — pick up ideas and plants for the estate grounds.',                         'category' => 'Business',          'days' => 1, 'weeks' => 6,  'notes' => 'Meet the landscape designer at Stand 14.'],
            ['name' => 'Property Inspection in Bath',          'description' => 'Inspect the rental property and meet with the letting agent for the quarterly review.',         'category' => 'Business',          'days' => 1, 'weeks' => 8,  'notes' => 'Bring the inspection checklist.'],
            ['name' => 'Sailing Weekend in Cornwall',          'description' => 'Weekend sailing trip from Falmouth with an overnight mooring in Fowey.',                        'category' => 'Adventure',         'days' => 3, 'weeks' => 9,  'notes' => 'Check weather forecast before departure.'],
            ['name' => 'Charity Gala in Birmingham',           'description' => 'Black-tie charity dinner at the Grand Hotel in aid of local children\'s hospice.',              'category' => 'Cultural',          'days' => 1, 'weeks' => 11, 'notes' => 'Table 7 — party of four.'],
            ['name' => 'Half-Term Holiday in Devon',           'description' => 'Family holiday on the Devon coast — surfing, walking, and cream teas.',                         'category' => 'Family Vacation',   'days' => 5, 'weeks' => 13, 'notes' => 'Farmhouse booked with pool.'],
            ['name' => 'Business Meeting in Manchester',       'description' => 'Full-day meeting with the accountants at their Deansgate office.',                              'category' => 'Business',          'days' => 1, 'weeks' => 15, 'notes' => 'Bring last quarter\'s financial reports.'],
            ['name' => 'Wedding in the Scottish Highlands',    'description' => 'Attend a friend\'s wedding at a castle near Inverness, with two nights\' accommodation.',       'category' => 'Family Vacation',   'days' => 3, 'weeks' => 17, 'notes' => 'Gift posted separately.'],
            ['name' => 'Spa Retreat in the Peak District',     'description' => 'Two-night spa break at a country hotel for some rest and relaxation.',                          'category' => 'Weekend Getaway',   'days' => 2, 'weeks' => 18, 'notes' => 'Spa treatments booked for Saturday.'],
            ['name' => 'Art Exhibition in Liverpool',          'description' => 'Private viewing at the Tate Liverpool, followed by dinner on the waterfront.',                  'category' => 'Cultural',          'days' => 1, 'weeks' => 20, 'notes' => 'Invitations in the study drawer.'],
            ['name' => 'Christmas Market in York',             'description' => 'Day trip to browse the York Christmas market and pick up festive gifts.',                       'category' => 'Leisure',           'days' => 1, 'weeks' => 21, 'notes' => 'Parking pre-booked at the Park & Ride.'],
        ];

        foreach ($trips as $tripData) {
            $startDate  = $now->copy()->addWeeks($tripData['weeks'])->startOfDay()->setTime(rand(7, 10), [0, 30][array_rand([0, 30])]);
            $endDate    = $startDate->copy()->addDays($tripData['days'])->setTime(rand(16, 19), 0);
            $bufferDays = rand(3, 7);
            $bufferAlert = $startDate->copy()->subDays($bufferDays);

            $category = $tripCategories->firstWhere('name', $tripData['category']) ?? $tripCategories->random();
            $status   = Trip::resolveStatusFor($startDate, $endDate);

            Trip::create([
                'name'             => $tripData['name'],
                'description'      => $tripData['description'],
                'start_date'       => $startDate,
                'end_date'         => $endDate,
                'trip_category_id' => $category->id,
                'buffer_alert'     => $bufferAlert,
                'status_id'        => $status->id,
                'notes'            => $tripData['notes'],
            ]);
        }
    }
}
