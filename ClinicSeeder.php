<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Clinic::create([
            'name' => 'The Minute Medical',
            'is_active' => true,
            'location' => 'Homs - Alhamra',
            'mobile' => '0994663365',
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_open' => true,
        ]);
        Clinic::create([
            'name' => 'First Priority Medical',
            'is_active' => true,
            'location' => 'Homs - Alwadi',
            'mobile' => '0994665635',
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_open' => true,
        ]);
        Clinic::create([
            'name' => 'Treatment Solutions',
            'is_active' => true,
            'location' => 'Homs - Sokara',
            'mobile' => '0996535594',
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_open' => true,
        ]);
        Clinic::create([
            'name' => 'The Vitality Visit',
            'is_active' => true,
            'location' => 'Homs - AlGotah',
            'mobile' => '0996332254',
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_open' => true,
        ]);
        Clinic::create([
            'name' => 'Care for Health',
            'is_active' => true,
            'location' => 'Homs - Akrama',
            'mobile' => '0994556632',
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_open' => true,
        ]);
        Clinic::create([
            'name' => 'Health Medical',
            'is_active' => true,
            'location' => 'Homs - Alzahra',
            'mobile' => '0994486632',
            'open_time' => '08:00:00',
            'close_time' => '16:00:00',
            'is_open' => true,
        ]);
    }
}
