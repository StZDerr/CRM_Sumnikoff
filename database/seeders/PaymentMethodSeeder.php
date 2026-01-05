<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['title' => 'Безналичный', 'slug' => null, 'sort_order' => 1],
            ['title' => 'Наличный', 'slug' => null, 'sort_order' => 2],
            ['title' => 'Оплата по факту', 'slug' => null, 'sort_order' => 3],
            ['title' => 'Кредит/Рассрочка', 'slug' => null, 'sort_order' => 4],
        ];

        foreach ($methods as $m) {
            $slug = $m['slug'] ?? Str::slug($m['title']);
            PaymentMethod::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $m['title'],
                    'slug' => $slug,
                    'sort_order' => $m['sort_order'] ?? null,
                ]
            );
        }
    }
}
