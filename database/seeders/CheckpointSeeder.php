<?php

namespace Database\Seeders;

use App\Models\Checkpoint;
use App\Models\Folder;
use Illuminate\Database\Seeder;

class CheckpointSeeder extends Seeder
{
    /**
     * Real, recognizable UK locations with accurate coordinates — chosen to
     * match the destinations used in TripSeeder, so seeded trips get route
     * maps that actually land in the right place instead of random/fake
     * Faker addresses that don't correspond to anywhere real.
     */
    private const LOCATIONS = [
        ['location' => 'Waverley Bridge', 'address' => 'Waverley Bridge, Edinburgh EH1 1BQ, UK', 'latitude' => 55.9520, 'longitude' => -3.1898],
        ['location' => 'Leith Docks', 'address' => 'Leith, Edinburgh EH6 6RQ, UK', 'latitude' => 55.9800, 'longitude' => -3.1750],
        ['location' => 'Royal Hall', 'address' => 'Royal Hall, Harrogate HG1 2RD, UK', 'latitude' => 53.9938, 'longitude' => -1.5350],
        ['location' => 'Windermere Lakeside', 'address' => 'Lakeside, Windermere, Cumbria LA23 3AS, UK', 'latitude' => 54.3701, 'longitude' => -2.9060],
        ['location' => 'Leicester Square', 'address' => 'Leicester Square, London WC2H 7NA, UK', 'latitude' => 51.5101, 'longitude' => -0.1300],
        ['location' => 'Tate Modern', 'address' => 'Bankside, London SE1 9TG, UK', 'latitude' => 51.5076, 'longitude' => -0.0994],
        ['location' => 'Cirencester Market Place', 'address' => 'Market Place, Cirencester GL7 2NW, UK', 'latitude' => 51.7189, 'longitude' => -1.9666],
        ['location' => 'Hampton Court Palace', 'address' => 'Hampton Court Palace, Surrey KT8 9AU, UK', 'latitude' => 51.4039, 'longitude' => -0.3369],
        ['location' => 'Royal Crescent', 'address' => 'Royal Crescent, Bath BA1 2LR, UK', 'latitude' => 51.3856, 'longitude' => -2.3670],
        ['location' => 'Falmouth Harbour', 'address' => 'Harbourside, Falmouth TR11 3DH, UK', 'latitude' => 50.1533, 'longitude' => -5.0728],
        ['location' => 'Fowey Harbour', 'address' => 'The Quay, Fowey PL23 1AT, UK', 'latitude' => 50.3352, 'longitude' => -4.6349],
        ['location' => 'Grand Hotel Birmingham', 'address' => 'Colmore Row, Birmingham B3 2DA, UK', 'latitude' => 52.4814, 'longitude' => -1.8998],
        ['location' => 'Exeter Quay', 'address' => 'The Quay, Exeter EX2 4AP, UK', 'latitude' => 50.7130, 'longitude' => -3.5330],
        ['location' => 'Deansgate', 'address' => 'Deansgate, Manchester M3 2BW, UK', 'latitude' => 53.4770, 'longitude' => -2.2500],
        ['location' => 'Inverness Castle', 'address' => 'Castle Hill, Inverness IV2 3EL, UK', 'latitude' => 57.4776, 'longitude' => -4.2242],
        ['location' => 'Bakewell', 'address' => 'Bridge Street, Bakewell, Peak District DE45 1DS, UK', 'latitude' => 53.2153, 'longitude' => -1.6675],
        ['location' => 'Albert Dock', 'address' => 'Albert Dock, Liverpool L3 4AA, UK', 'latitude' => 53.4009, 'longitude' => -2.9925],
        ['location' => 'York Minster', 'address' => 'Deangate, York YO1 7HH, UK', 'latitude' => 53.9623, 'longitude' => -1.0820],
    ];

    public function run(): void
    {
        $folderIds = Folder::pluck('id');

        foreach (self::LOCATIONS as $location) {
            Checkpoint::create([
                'location' => $location['location'],
                'address' => $location['address'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'coordinates' => "{$location['latitude']},{$location['longitude']}",
                'folder_id' => $folderIds->isNotEmpty() ? $folderIds->random() : null,
            ]);
        }
    }
}
