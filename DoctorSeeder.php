<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $n = 1;
        $jj=1;
        $clinics = Clinic::get()->pluck(['id'])->toArray();
        $specialists = Specialist::get()->pluck(['id'])->toArray();
        $gender = [1, 2];

        for ($i = 1; $i < 50; $i++) {
            $s = \App\Models\User::factory()->count(1)->create();
            $user = User::create([
                'name' => 'Dr.' . $s[0]['name'],
                'email' => 'd' . $jj . '@d.d',
                'role' => UserTypeEnum::Doctor,
                'password' => bcrypt(12345678),
            ]);

            Doctor::create([
                'is_active' => true,
                'image_path' => '/uploads/images/dr/d' . $n . '.jpg',
                'clinic_id' => $clinics[array_rand($clinics)],
                'specialist_id' => $specialists[array_rand($specialists)],
                'mobile' => '0955193343',
                'gender' => $gender[array_rand($gender)],
                'session_duration' => '00:30:00',
                'old_session_duration' => '00:30:00',
                'id' => $user->id,
            ]);
            $n++;
            $jj++;
            if ($n == 10) $n = 1;
        }
    }
}
