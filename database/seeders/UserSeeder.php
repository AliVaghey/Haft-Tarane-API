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
        $admins = User::factory(3)->create([
            'access_type' => UserAccessType::Admin,
        ]);

        //Agency user :
        $agencies = User::factory(9)->create([
            'access_type' => UserAccessType::Agency,
        ]);
        $i = 0;
        foreach ($admins as $admin) {
            for ($j = 0; $j < 3; $j++, $i++) {
                AgencyInfoController::makeModel($agencies[$i], $admin);
            }
        }

        //Normal user :
        User::factory(10)->create();
    }
}
