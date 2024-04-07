<?php

namespace Database\Seeders;

use App\Enums\UserAccessType;
use App\Http\Controllers\AgencyInfoController;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Admin user :
        User::factory(1)->create([
            'access_type' => UserAccessType::Admin,
        ]);

        //Agency user :
        User::factory(2)->create([
            'access_type' => UserAccessType::Agency,
        ]);

        $agencies = User::where('access_type', 'agency')->get();
        foreach ($agencies as $agency) {
            AgencyInfoController::makeModel($agency, $agency);
        }

        //Normal user :
        User::factory(10)->create();
    }
}
