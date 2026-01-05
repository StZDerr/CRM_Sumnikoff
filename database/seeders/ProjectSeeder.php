<?php

namespace Database\Seeders;

use App\Models\Importance;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Гарантируем наличие нужных справочников и организаций
        $this->call([
            \Database\Seeders\ImportanceSeeder::class,
            \Database\Seeders\PaymentMethodSeeder::class,
        ]);

        $orgNames = ['Acme LLC', 'Test Company', 'Sumnikoff Group', 'Rostov Shipping', 'Novatek Solutions'];
        foreach ($orgNames as $n) {
            Organization::firstOrCreate(['name_short' => $n], ['name_full' => $n]);
        }

        $marketer = User::where('login', 'StZD')->first();
        $impHigh = Importance::where('name', 'Высокая')->first();
        $impMid = Importance::where('name', 'Средняя')->first();
        $impLow = Importance::where('name', 'Низкая')->first();

        // Исправлено: используем 'title' (в вашей модели PaymentMethod нет колонки name)
        $pmBank = PaymentMethod::where('title', 'Безналичный')->first();
        $pmCash = PaymentMethod::where('title', 'Наличный')->first();
        $pmFact = PaymentMethod::where('title', 'Оплата по факту')->first();

        $items = [
            ['title' => 'Website redesign', 'org' => 'Acme LLC', 'city' => 'Москва', 'importance' => $impMid, 'amount' => '500000.00', 'date' => Carbon::now()->subMonths(6), 'payment' => $pmBank, 'due' => 15, 'received' => '500000.00', 'closed' => Carbon::now()->subMonths(1)],
            ['title' => 'Mobile app', 'org' => 'Test Company', 'city' => 'Санкт-Петербург', 'importance' => $impHigh, 'amount' => '1200000.00', 'date' => Carbon::now()->subMonths(8), 'payment' => $pmCash, 'due' => 10, 'received' => '1100000.00', 'closed' => null],
            ['title' => 'ERP integration', 'org' => 'Sumnikoff Group', 'city' => 'Москва', 'importance' => $impHigh, 'amount' => '2500000.00', 'date' => Carbon::now()->subYear(), 'payment' => $pmBank, 'due' => 20, 'received' => '2500000.00', 'closed' => Carbon::now()->subMonths(2)],
            ['title' => 'Logistics API', 'org' => 'Rostov Shipping', 'city' => 'Ростов-на-Дону', 'importance' => $impMid, 'amount' => '800000.00', 'date' => Carbon::now()->subMonths(4), 'payment' => $pmFact, 'due' => 25, 'received' => '780000.00', 'closed' => null],
            ['title' => 'Cloud migration', 'org' => 'Novatek Solutions', 'city' => 'Санкт-Петербург', 'importance' => $impMid, 'amount' => '350000.00', 'date' => Carbon::now()->subMonths(3), 'payment' => $pmBank, 'due' => 5, 'received' => '100000.00', 'closed' => null],
            ['title' => 'CRM deployment', 'org' => 'Sumnikoff Group', 'city' => 'Москва', 'importance' => $impHigh, 'amount' => '950000.00', 'date' => Carbon::now()->subMonths(10), 'payment' => $pmBank, 'due' => 15, 'received' => '950000.00', 'closed' => Carbon::now()->subMonths(5)],
            ['title' => 'SEO campaign', 'org' => 'Acme LLC', 'city' => 'Москва', 'importance' => $impLow, 'amount' => '120000.00', 'date' => Carbon::now()->subMonths(2), 'payment' => $pmFact, 'due' => 1, 'received' => '30000.00', 'closed' => null],
            ['title' => 'Support retainer', 'org' => 'Test Company', 'city' => 'Санкт-Петербург', 'importance' => $impLow, 'amount' => '60000.00', 'date' => Carbon::now()->subMonth(), 'payment' => $pmBank, 'due' => 5, 'received' => '15000.00', 'closed' => null],
            ['title' => 'Offshore development', 'org' => 'Rostov Shipping', 'city' => 'Ростов-на-Дону', 'importance' => $impMid, 'amount' => '400000.00', 'date' => Carbon::now()->subMonths(7), 'payment' => $pmCash, 'due' => 12, 'received' => '400000.00', 'closed' => Carbon::now()->subMonths(3)],
            ['title' => 'R&D prototype', 'org' => 'Novatek Solutions', 'city' => 'Санкт-Петербург', 'importance' => $impLow, 'amount' => '90000.00', 'date' => Carbon::now()->subDays(15), 'payment' => $pmBank, 'due' => 20, 'received' => '0.00', 'closed' => null],
        ];

        foreach ($items as $it) {
            $org = Organization::where('name_short', $it['org'])->first();
            Project::updateOrCreate(
                ['title' => $it['title']],
                [
                    'organization_id' => $org?->id,
                    'city' => $it['city'],
                    'marketer_id' => $marketer?->id,
                    'importance_id' => $it['importance']?->id,
                    'contract_amount' => $it['amount'],
                    'contract_date' => $it['date'],
                    'payment_method_id' => $it['payment']?->id,
                    'payment_due_day' => $it['due'],
                    'debt' => number_format(max(0, (float) $it['amount'] - (float) $it['received']), 2, '.', ''),
                    'comment' => 'Сгенерировано сидером',
                    'received_total' => $it['received'],
                    'received_calculated_at' => $it['date'],
                    'balance' => (float) $it['amount'] - (float) $it['received'],
                    'balance_calculated_at' => $it['date'],
                    'closed_at' => $it['closed'],
                ]
            );
        }
    }
}
