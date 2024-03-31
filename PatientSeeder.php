<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gender = [1, 2];
        $n = 1;
        $k = 1;
        $jj=1;
        $age = [15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25];
        for ($i = 1; $i < 50; $i++) {
            $s = \App\Models\User::factory()->count(1)->create();
            $t = \App\Models\User::factory()->count(1)->create();
            $user = User::create([
                'name' => $s[0]['name'],
                'email' => 'p' . $k . '@p.p',
                'role' => UserTypeEnum::Patient,
                'password' => bcrypt(12345678),
            ]);
            $k++;
            Patient::create([
                'is_active' => true,
                'image_path' => '/uploads/images/pt/p' . $jj . '.jpg',
                'mobile' => '0955193343',
                'gender' => $gender[array_rand($gender)],
                'id' => $user->id,
                'location' => $t[0]['name'],
                'age' => $age[array_rand($age)],

            ]);
            $n++;
            $jj++;
            if ($n == 10) $n = 1;
        }
    }
}
