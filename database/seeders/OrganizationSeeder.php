<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Organization::updateOrCreate(
            ['name_short' => 'Acme LLC'],
            [
                'name_full' => 'Acme Limited Liability Company',
                'phone' => '+7 (495) 123-45-67',
                'email' => 'info@acme.local',
                'inn' => '7701234567',
                'ogrnip' => null,
                'legal_address' => 'г. Москва, Красная площадь, д.1',
                'actual_address' => 'г. Москва, Лубянка, д.2',
                'account_number' => '40817810099910004312',
                'bank_name' => 'АО «ТестБанк»',
                'corr_account' => '30101810400000000225',
                'bic' => '044525225',
                'notes' => 'Ключевой поставщик',
                'campaign_status_id' => null,
                'campaign_source_id' => null,
            ]
        );

        Organization::updateOrCreate(
            ['name_short' => 'Test Company'],
            [
                'name_full' => 'Test Company LLC',
                'phone' => '+7 (812) 765-43-21',
                'email' => 'contact@test.local',
                'inn' => '7801234567',
                'ogrnip' => null,
                'legal_address' => 'г. Санкт-Петербург, Невский пр., д.10',
                'actual_address' => 'г. Санкт-Петербург, Мойка, д.12',
                'account_number' => '40817810500000001234',
                'bank_name' => 'Банк Тест',
                'corr_account' => '30101810100000000225',
                'bic' => '044030653',
                'notes' => 'Тестовая организация',
                'campaign_status_id' => null,
                'campaign_source_id' => null,
            ]
        );

        Organization::updateOrCreate(
            ['name_short' => 'Sumnikoff Group'],
            [
                'name_full' => 'Sumnikoff Group ООО',
                'phone' => '+7 (495) 555-12-34',
                'email' => 'office@sumnikoff.local',
                'inn' => '7723456789',
                'ogrnip' => null,
                'legal_address' => 'г. Москва, ул. Ленина, д.10',
                'actual_address' => 'г. Москва, ул. Ленина, д.10',
                'account_number' => '40817810099910005555',
                'bank_name' => 'ПАО «СумБанк»',
                'corr_account' => '30101810400000000999',
                'bic' => '044525999',
                'notes' => 'Материнская компания',
                'campaign_status_id' => null,
                'campaign_source_id' => null,
            ]
        );

        Organization::updateOrCreate(
            ['name_short' => 'Rostov Shipping'],
            [
                'name_full' => 'Rostov Shipping Ltd',
                'phone' => '+7 (863) 222-33-44',
                'email' => 'logistics@rostovship.local',
                'inn' => '6161234560',
                'ogrnip' => null,
                'legal_address' => 'г. Ростов-на-Дону, Морская ул., д.5',
                'actual_address' => 'г. Ростов-на-Дону, Морская ул., д.5',
                'account_number' => '40817810099910007777',
                'bank_name' => 'АО «ЮгБанк»',
                'corr_account' => '30101810400000000777',
                'bic' => '046577777',
                'notes' => 'Логистический партнёр',
                'campaign_status_id' => null,
                'campaign_source_id' => null,
            ]
        );

        Organization::updateOrCreate(
            ['name_short' => 'Novatek Solutions'],
            [
                'name_full' => 'Novatek Solutions ООО',
                'phone' => '+7 (812) 333-44-55',
                'email' => 'sales@novatek.local',
                'inn' => '7812345678',
                'ogrnip' => null,
                'legal_address' => 'г. Санкт-Петербург, Промышленная, д.3',
                'actual_address' => 'г. Санкт-Петербург, Промышленная, д.3',
                'account_number' => '40817810500000009999',
                'bank_name' => 'ПАО «СеверБанк»',
                'corr_account' => '30101810100000009999',
                'bic' => '044030999',
                'notes' => 'Потенциальный клиент',
                'campaign_status_id' => null,
                'campaign_source_id' => null,
            ]
        );
    }
}
