<?php

namespace Database\Seeders;

use App\Enums\TourStatus;
use App\Models\AgencyInfo;
use App\Models\Tour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencies = AgencyInfo::all();
        foreach ($agencies as $agency) {

            //Draft Tours :
            Tour::factory(5)->create([
                'agency_id' => $agency->id,
                'status' => TourStatus::Draft,
            ]);

            //Pending Tours :
            Tour::factory(5)->create([
                'agency_id' => $agency->id,
                'status' => TourStatus::Pending,
            ]);

            //Rejected Tours :
            Tour::factory(5)->create([
                'agency_id' => $agency->id,
                'status' => TourStatus::Rejected,
            ]);

            //Active Tours :
            Tour::factory(5)->create([
                'agency_id' => $agency->id,
                'status' => TourStatus::Active,
            ]);
        }
    }
}
