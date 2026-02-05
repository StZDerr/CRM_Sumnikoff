<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Новая', 'slug' => 'new', 'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'В работе', 'slug' => 'in_progress', 'color' => '#F59E0B', 'sort_order' => 2, 'is_default' => false],
            ['name' => 'На проверке', 'slug' => 'review', 'color' => '#8B5CF6', 'sort_order' => 3, 'is_default' => false],
            ['name' => 'Готово', 'slug' => 'done', 'color' => '#10B981', 'sort_order' => 4, 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            TaskStatus::updateOrCreate(['slug' => $status['slug']], $status);
        }
    }
}
