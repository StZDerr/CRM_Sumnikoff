<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DumpDataSeeder extends Seeder
{
    public function run(): void
    {
        // Отключаем FK на время вставки (MySQL)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // campaign_sources
        DB::table('campaign_sources')->insertOrIgnore([
            ['id' => 1, 'name' => 'Авито', 'slug' => 'avito', 'sort_order' => 1, 'created_at' => '2026-01-12 05:33:05', 'updated_at' => '2026-01-12 05:33:05'],
            ['id' => 2, 'name' => 'Телемаркетинг', 'slug' => 'telemarketing', 'sort_order' => 2, 'created_at' => '2026-01-12 05:33:12', 'updated_at' => '2026-01-12 05:33:12'],
            ['id' => 3, 'name' => 'Рекомендация (Илья Алексеевич)', 'slug' => 'rekomendaciia', 'sort_order' => 3, 'created_at' => '2026-01-12 05:33:19', 'updated_at' => '2026-01-12 06:13:40'],
            ['id' => 4, 'name' => 'Самообращение', 'slug' => 'samoobrashhenie', 'sort_order' => 4, 'created_at' => '2026-01-12 05:33:25', 'updated_at' => '2026-01-12 05:33:25'],
            ['id' => 5, 'name' => 'от Партнеров', 'slug' => 'ot-partnerov', 'sort_order' => 5, 'created_at' => '2026-01-12 05:33:31', 'updated_at' => '2026-01-12 05:33:31'],
            ['id' => 6, 'name' => 'Отдел продаж - аутсорс', 'slug' => 'otdel-prodaz-autsors', 'sort_order' => 6, 'created_at' => '2026-01-12 05:33:56', 'updated_at' => '2026-01-12 05:33:56'],
        ]);

        // campaign_statuses
        DB::table('campaign_statuses')->insertOrIgnore([
            ['id' => 1, 'name' => 'Активная', 'slug' => 'aktivnaia', 'sort_order' => 1, 'created_at' => '2026-01-12 05:32:27', 'updated_at' => '2026-01-12 05:32:27'],
            ['id' => 2, 'name' => 'Не активная', 'slug' => 'ne-aktivnaia', 'sort_order' => 2, 'created_at' => '2026-01-12 05:32:32', 'updated_at' => '2026-01-12 05:32:32'],
            ['id' => 3, 'name' => 'Заморозка', 'slug' => 'zamorozka', 'sort_order' => 3, 'created_at' => '2026-01-12 05:32:42', 'updated_at' => '2026-01-12 05:32:42'],
        ]);

        // bank_accounts
        DB::table('bank_accounts')->insertOrIgnore([
            [
                'id' => 1,
                'title' => 'тест',
                'account_number' => '02490432809480392',
                'correspondent_account' => '432432',
                'bik' => null,
                'inn' => '2311195772',
                'bank_name' => 'Аььфы',
                'notes' => null,
                'balance' => 52000.00,
                'created_at' => '2026-01-12 05:08:01',
                'updated_at' => '2026-01-13 08:38:59',
                'deleted_at' => null,
            ],
        ]);

        // expense_categories
        DB::table('expense_categories')->insertOrIgnore([
            ['id' => 1, 'title' => 'Оплата рекламы', 'slug' => 'oplata-reklamy', 'sort_order' => 1, 'created_at' => '2026-01-12 05:11:59', 'updated_at' => '2026-01-12 05:11:59'],
            ['id' => 2, 'title' => 'Обеспечение офиса', 'slug' => 'obespecenie-ofisa', 'sort_order' => 2, 'created_at' => '2026-01-12 05:12:37', 'updated_at' => '2026-01-12 05:12:37'],
            ['id' => 3, 'title' => 'Коммунальные платежи и аренда', 'slug' => 'kommunalnye-platezi-i-arenda', 'sort_order' => 3, 'created_at' => '2026-01-12 05:13:15', 'updated_at' => '2026-01-12 05:13:15'],
            ['id' => 4, 'title' => 'Выплата ЗП', 'slug' => 'vyplata-zp', 'sort_order' => 4, 'created_at' => '2026-01-12 05:13:53', 'updated_at' => '2026-01-12 05:13:53'],
        ]);

        // importances
        DB::table('importances')->insertOrIgnore([
            ['id' => 1, 'name' => 'Низкая', 'slug' => 'nizkaia', 'color' => '#58a3fd', 'sort_order' => 1, 'created_at' => '2026-01-12 05:31:03', 'updated_at' => '2026-01-12 05:31:03'],
            ['id' => 2, 'name' => 'Средняя', 'slug' => 'sredniaia', 'color' => '#3dff91', 'sort_order' => 2, 'created_at' => '2026-01-12 05:31:14', 'updated_at' => '2026-01-12 05:31:14'],
            ['id' => 3, 'name' => 'Высокая', 'slug' => 'vysokaia', 'color' => '#ff0a23', 'sort_order' => 3, 'created_at' => '2026-01-12 05:31:26', 'updated_at' => '2026-01-12 05:31:26'],
            ['id' => 4, 'name' => 'Критическая', 'slug' => 'kriticeskaia', 'color' => '#750000', 'sort_order' => 4, 'created_at' => '2026-01-12 05:31:40', 'updated_at' => '2026-01-12 05:31:40'],
        ]);

        // invoice_statuses
        DB::table('invoice_statuses')->insertOrIgnore([
            ['id' => 1, 'name' => 'Оплачено', 'slug' => 'oplaceno', 'sort_order' => 1, 'created_at' => '2026-01-12 05:08:19', 'updated_at' => '2026-01-12 05:08:19'],
            ['id' => 2, 'name' => 'Частичная оплата', 'slug' => 'casticnaia-oplata', 'sort_order' => 2, 'created_at' => '2026-01-12 05:08:30', 'updated_at' => '2026-01-12 05:08:30'],
            ['id' => 3, 'name' => 'Бартер', 'slug' => 'barter', 'sort_order' => 3, 'created_at' => '2026-01-12 05:08:35', 'updated_at' => '2026-01-12 05:08:35'],
            ['id' => 4, 'name' => 'Долг', 'slug' => 'dolg', 'sort_order' => 4, 'created_at' => '2026-01-12 05:08:57', 'updated_at' => '2026-01-12 05:08:57'],
        ]);

        // payment_categories
        DB::table('payment_categories')->insertOrIgnore([
            ['id' => 1, 'title' => 'Регулярный платеж', 'slug' => 'reguliarnyi-platez', 'sort_order' => 1, 'created_at' => '2026-01-12 05:10:26', 'updated_at' => '2026-01-12 05:10:26'],
            ['id' => 2, 'title' => 'Разовый платеж', 'slug' => 'razovyi-platez', 'sort_order' => 2, 'created_at' => '2026-01-12 05:10:34', 'updated_at' => '2026-01-12 05:10:34'],
            ['id' => 3, 'title' => 'Оплата хостинга и домена', 'slug' => 'oplata-xostinga-i-domena', 'sort_order' => 3, 'created_at' => '2026-01-12 05:11:25', 'updated_at' => '2026-01-12 05:11:25'],
            ['id' => 4, 'title' => 'Бюджеты для рекламы', 'slug' => 'biudzety-dlia-reklamy', 'sort_order' => 4, 'created_at' => '2026-01-12 05:11:39', 'updated_at' => '2026-01-12 05:11:39'],
        ]);

        // payment_methods
        DB::table('payment_methods')->insertOrIgnore([
            ['id' => 1, 'title' => 'Р/С', 'slug' => 'rs', 'sort_order' => 1, 'created_at' => '2026-01-12 05:30:04', 'updated_at' => '2026-01-12 05:30:04'],
            ['id' => 2, 'title' => 'Перевод', 'slug' => 'perevod', 'sort_order' => 2, 'created_at' => '2026-01-12 05:30:21', 'updated_at' => '2026-01-12 05:30:21'],
            ['id' => 3, 'title' => 'Бартер', 'slug' => 'barter', 'sort_order' => 3, 'created_at' => '2026-01-12 05:30:26', 'updated_at' => '2026-01-12 05:30:26'],
            ['id' => 4, 'title' => 'Наличные', 'slug' => 'nalicnye', 'sort_order' => 4, 'created_at' => '2026-01-12 05:30:32', 'updated_at' => '2026-01-12 05:30:32'],
        ]);

        // specialties
        DB::table('specialties')->insertOrIgnore([
            ['id' => 1, 'name' => 'Стажер', 'salary' => 20000, 'active' => 1, 'created_at' => '2026-01-14 05:17:15', 'updated_at' => '2026-01-14 05:17:15'],
            ['id' => 2, 'name' => 'Нач. спец', 'salary' => 25000, 'active' => 1, 'created_at' => '2026-01-14 05:17:15', 'updated_at' => '2026-01-14 05:17:15'],
            ['id' => 3, 'name' => 'Спец', 'salary' => 30000, 'active' => 1, 'created_at' => '2026-01-14 05:17:15', 'updated_at' => '2026-01-14 05:17:15'],
            ['id' => 4, 'name' => 'Опытный спец', 'salary' => 40000, 'active' => 1, 'created_at' => '2026-01-14 05:17:15', 'updated_at' => '2026-01-14 05:17:15'],
            ['id' => 5, 'name' => 'Ведущий спец', 'salary' => 50000, 'active' => 1, 'created_at' => '2026-01-14 05:17:15', 'updated_at' => '2026-01-14 05:17:15'],
        ]);

        // organizations
        DB::table('organizations')->insertOrIgnore([
            [
                'id' => 1,
                'name_full' => 'Трактор Тула АГРОРЕМТОРГ',
                'name_short' => 'ИП Войтеховский В.А.',
                'phone' => '8-953-440-61-61',
                'email' => 'brilkov69@bk.ru',
                'inn' => '710408447376',
                'ogrnip' => '321710000074895',
                'legal_address' => '300026, г.Тула, Калужское шоссе, д.1, кв. 94',
                'actual_address' => '300026, г.Тула, Калужское шоссе, д.1, кв. 94',
                'account_number' => '40802810202920006476',
                'bank_name' => 'АО «Альфа Банк» г. Москва',
                'corr_account' => '30101810200000000593',
                'bic' => '044525593',
                'notes' => 'Счета/ напоминания в WA на контакт \"Магазин Новый\"',
                'campaign_status_id' => 1,
                'campaign_source_id' => 3,
                'created_by' => 3,
                'updated_by' => 3,
                'created_at' => '2026-01-12 06:17:44',
                'updated_at' => '2026-01-12 06:20:31',
            ],
        ]);

        // stages
        DB::table('stages')->insertOrIgnore([
            ['id' => 1, 'name' => 'СЕО (SEO)', 'slug' => 'seo-seo', 'sort_order' => 1, 'created_at' => '2026-01-12 05:29:16', 'updated_at' => '2026-01-12 05:29:16'],
            ['id' => 2, 'name' => 'Авито', 'slug' => 'avito', 'color' => '#1e334d', 'sort_order' => 2, 'created_at' => '2026-01-12 05:29:33', 'updated_at' => '2026-01-12 05:29:33'],
        ]);

        // projects
        DB::table('projects')->insertOrIgnore([
            [
                'id' => 1,
                'title' => 'Трактор Тула',
                'organization_id' => 1,
                'city' => 'Тула',
                'marketer_id' => 5,
                'importance_id' => 1,
                'contract_amount' => 13000.00,
                'debt' => 0.00,
                'received_total' => 52000.00,
                'balance' => 52000.00,
                'balance_calculated_at' => '2026-01-13 08:38:59',
                'received_calculated_at' => '2026-01-13 08:38:59',
                'payment_method_id' => 1,
                'contract_date' => '2026-01-01',
                'created_by' => 3,
                'updated_by' => 1,
                'comment' => 'Сайты и гео- сервисы (карты) ...',
                'created_at' => '2026-01-12 06:31:15',
                'updated_at' => '2026-01-13 08:38:59',
            ],
        ]);

        // project_stage
        DB::table('project_stage')->insertOrIgnore([
            ['id' => 1, 'project_id' => 1, 'stage_id' => 1, 'sort_order' => 1, 'created_at' => '2026-01-12 06:31:15', 'updated_at' => '2026-01-13 08:32:25'],
        ]);

        // users (минимально: вставляем поля, которые есть в проекте)
        DB::table('users')->insertOrIgnore([
            ['id' => 1, 'name' => 'StZD', 'login' => 'StZD', 'email' => null, 'email_verified_at' => '2026-01-14 05:17:19', 'password' => '$2y$12$qjtYp.fpPuhdfkbRHNSB9.W08GJctOopIOKgFljMXByh9frLFvYdK', 'role' => 'admin', 'created_at' => '2026-01-14 05:17:19', 'updated_at' => '2026-01-14 05:17:19', 'specialty_id' => null, 'salary_override' => null, 'is_department_head' => 0, 'individual_bonus_percent' => null],
            ['id' => 2, 'name' => 'Илья Алексеевич Сумников', 'login' => 'ilasumnikov', 'email' => null, 'email_verified_at' => null, 'password' => '$2y$12$PptHF827CnRc0Yc2ZEp69eQpOqWhrdzqcRRhogWowlTWvjTwwSFd.', 'role' => 'manager', 'created_at' => '2026-01-14 05:39:17', 'updated_at' => '2026-01-14 05:39:17', 'specialty_id' => null, 'salary_override' => 100000, 'is_department_head' => 1, 'individual_bonus_percent' => 10],
            ['id' => 3, 'name' => 'Александра Воронина', 'login' => 'avoronina', 'email' => null, 'email_verified_at' => null, 'password' => '$2y$12$JAHxGJY/zuNd9TORMydGSO3HGmHBCZpXa92XHIfHc7zCN.zb51Ja2', 'role' => 'admin', 'created_at' => '2026-01-14 05:39:51', 'updated_at' => '2026-01-14 05:39:51', 'specialty_id' => null, 'salary_override' => 100000, 'is_department_head' => 1, 'individual_bonus_percent' => 10],
            ['id' => 4, 'name' => 'Владислав Эдуардович Гречишкин', 'login' => 'vgrechishkin', 'email' => null, 'email_verified_at' => null, 'password' => '$2y$12$KvEAzD8Qd5gv8fUAaP9sUOlAhwwCrlRWXpE8EbC2PGlYGidDhinFm', 'role' => 'admin', 'created_at' => '2026-01-14 05:40:25', 'updated_at' => '2026-01-14 05:40:25', 'specialty_id' => null, 'salary_override' => 100000, 'is_department_head' => 1, 'individual_bonus_percent' => 0],
            ['id' => 5, 'name' => 'Гофман Александра Евгеньевна', 'login' => 'gofman', 'email' => 'gofmanyusha@yandex.ru', 'email_verified_at' => null, 'password' => '$2y$12$3ScC3gGIQLYB8QIMfvW/A.XAdstgXkpZhQHxoxWX7Pw5/8QHfpZFe', 'role' => 'manager', 'created_at' => '2026-01-14 05:42:20', 'updated_at' => '2026-01-14 05:42:20', 'specialty_id' => null, 'salary_override' => 70000, 'is_department_head' => 1, 'individual_bonus_percent' => 7],
        ]);

        // expenses (sample)
        DB::table('expenses')->insertOrIgnore([
            [
                'id' => 1,
                'expense_date' => '2026-01-12 15:37:00',
                'amount' => 247.00,
                'expense_category_id' => 2,
                'organization_id' => null,
                'payment_method_id' => 2,
                'bank_account_id' => null,
                'project_id' => null,
                'status' => 'paid',
                'currency' => 'RUB',
                'description' => 'Оплата чая в офис',
                'created_at' => '2026-01-12 09:37:44',
                'updated_at' => '2026-01-12 09:38:28',
            ],
        ]);

        // Включаем проверки FK обратно
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
