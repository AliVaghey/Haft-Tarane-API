<?php

namespace Database\Seeders;

use App\Enums\UserAccessType;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\AgencyInfo;

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
                AgencyInfo::factory()->create([
                    'user_id' => $agencies[$i]->id,
                    'admin_id' => $admin->id,
                ]);
            }
        }

        //Normal user :
        User::factory(10)->create();
    }
}
