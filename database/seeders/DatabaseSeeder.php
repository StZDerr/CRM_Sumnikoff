<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            \Database\Seeders\FirstUserSeeder::class,
            \Database\Seeders\OrganizationSeeder::class,
            \Database\Seeders\ProjectSeeder::class,
            \Database\Seeders\PaymentCategorySeeder::class,
            \Database\Seeders\InvoiceStatusSeeder::class,
            \Database\Seeders\TaskStatusSeeder::class,
        ]);
    }
}
