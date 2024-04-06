<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('access_type', 'admin')->first();
        Hotel::factory()
            ->count(10)
            ->create([
                'admin_id' => $admin->id,
            ]);
    }
}
