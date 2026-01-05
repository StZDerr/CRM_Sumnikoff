<?php

namespace Database\Seeders;

use App\Models\Importance;
use Illuminate\Database\Seeder;

class ImportanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = ['Низкая', 'Средняя', 'Высокая'];

        foreach ($names as $i => $name) {
            Importance::updateOrCreate(
                ['name' => $name],
                ['sort_order' => $i + 1]
            );
        }
    }
}
