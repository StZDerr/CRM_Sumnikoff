<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name' => 'Стажер', 'salary' => 20000, 'active' => true],
            ['name' => 'Нач. спец', 'salary' => 25000, 'active' => true],
            ['name' => 'Спец', 'salary' => 30000, 'active' => true],
            ['name' => 'Опытный спец', 'salary' => 40000, 'active' => true],
            ['name' => 'Ведущий спец', 'salary' => 50000, 'active' => true],
        ];

        foreach ($specialties as $spec) {
            Specialty::updateOrCreate(['name' => $spec['name']], $spec);
        }
    }
}
