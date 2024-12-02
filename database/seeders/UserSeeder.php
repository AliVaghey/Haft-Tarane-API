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
        //Super Admin :
        $superAdmin = User::factory(1)->create([
            'access_type' => UserAccessType::SuperAdmin,
            'username' => "farahani",
            'first_name_fa' => "حمیدرضا",
            'first_name_en' => "HamidReza",
            'last_name_fa' => "فراهانی",
            'last_name_en' => "Farahani",
            'phone' => "09100940950",
            'gender' => "male",
        ])->first();
        AgencyInfo::factory(1)->create([
            'user_id' => $superAdmin->id,
            'admin_id' => $superAdmin->id,
        ]);

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
