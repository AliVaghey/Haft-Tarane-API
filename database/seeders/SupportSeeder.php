<?php

namespace Database\Seeders;

use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencies = User::where('access_type', 'agency')->get();
        foreach ($agencies as $agency) {
            Support::factory(10)->create([
                'agency_id' => $agency->id,
            ]);
        }
    }
}
