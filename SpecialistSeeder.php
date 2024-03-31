<?php

namespace Database\Seeders;

use App\Models\Specialist;
use Illuminate\Database\Seeder;

class SpecialistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Specialist::create([
            'name' => 'Orthopedic',
            'is_active' => true,
            'image_path' => '/uploads/images/sp/adem.png',
        ]);
        Specialist::create([
            'name' => 'Dentist',
            'is_active' => true,
            'image_path' => '/uploads/images/sp/snan.png',
        ]);
        Specialist::create([
            'name' => 'Neurology',
            'is_active' => true,
            'image_path' => '/uploads/images/sp/akel.png',
        ]);
        Specialist::create([
            'name' => 'Cardiologist',
            'is_active' => true,
            'image_path' => '/uploads/images/sp/aleb.png',
        ]);
    }
}
