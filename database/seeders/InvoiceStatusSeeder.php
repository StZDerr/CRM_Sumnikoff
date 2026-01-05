<?php

namespace Database\Seeders;

use App\Models\InvoiceStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InvoiceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Черновик', 'sort_order' => 1],
            ['name' => 'Отправлен', 'sort_order' => 2],
            ['name' => 'Оплачен', 'sort_order' => 3],
            ['name' => 'Просрочен', 'sort_order' => 4],
            ['name' => 'Аннулирован', 'sort_order' => 5],
        ];

        foreach ($items as $it) {
            $slug = Str::slug($it['name']);
            InvoiceStatus::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $it['name'],
                    'slug' => $slug,
                    'sort_order' => $it['sort_order'] ?? null,
                ]
            );
        }
    }
}
