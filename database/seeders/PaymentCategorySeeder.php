<?php

namespace Database\Seeders;

use App\Models\PaymentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['title' => 'Основная оплата', 'sort_order' => 1],
            ['title' => 'Аванс', 'sort_order' => 2],
            ['title' => 'Возврат', 'sort_order' => 3],
            ['title' => 'Комиссия', 'sort_order' => 4],
            ['title' => 'Погашение долга', 'sort_order' => 5],
            ['title' => 'Вознаграждение', 'sort_order' => 6],
            ['title' => 'Прочее', 'sort_order' => 7],
        ];

        foreach ($items as $it) {
            $slug = Str::slug($it['title']);
            PaymentCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $it['title'],
                    'slug' => $slug,
                    'sort_order' => $it['sort_order'] ?? null,
                ]
            );
        }
    }
}
