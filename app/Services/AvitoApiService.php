<?php

namespace App\Services;

use App\Models\AvitoAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AvitoApiService
{
    public function issueClientCredentialsToken(string $clientId, string $clientSecret): array
    {
        $response = Http::asForm()->post($this->baseUrl().'/token/', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Не удалось получить токен Avito: HTTP '.$response->status());
        }

        $data = $response->json();
        $accessToken = data_get($data, 'access_token');
        if (! $accessToken) {
            throw new \RuntimeException('Avito не вернул access_token.');
        }

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'access_token' => $accessToken,
            'token_type' => data_get($data, 'token_type', 'Bearer'),
            'refresh_token' => data_get($data, 'refresh_token'),
            'expires_at' => now()->addSeconds((int) data_get($data, 'expires_in', 86400))->toDateTimeString(),
        ];
    }

    public function syncAccount(AvitoAccount $account): array
    {
        $oauth = $account->oauth_data ?? [];
        $previousStats = $account->stats_data ?? [];
        if (! data_get($oauth, 'client_id') || ! data_get($oauth, 'client_secret')) {
            throw new \RuntimeException('В аккаунте отсутствуют client_id/client_secret.');
        }

        if ($this->tokenExpired($oauth)) {
            $oauth = $this->issueClientCredentialsToken((string) $oauth['client_id'], (string) $oauth['client_secret']);
        }

        $token = (string) data_get($oauth, 'access_token');
        $profile = $this->getProfile($token);
        $userId = (int) data_get($profile, 'id');

        if ($userId <= 0) {
            throw new \RuntimeException('Не удалось определить user_id аккаунта Avito.');
        }

        $balance = $this->resolveBalance($token, $userId);
        $operations = $this->getOperationsHistory($token, now()->subDays(14), now());
        $cpaBalance = $this->getCpaBalanceInfo($token);
        $statsError = null;
        $spendingV2Error = null;

        try {
            $todayStats = $this->getTodayStats($token, $userId);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'HTTP 429')) {
                $todayStats = [
                    'views' => (int) data_get($previousStats, 'views_today', 0),
                    'contacts' => (int) data_get($previousStats, 'contacts_today', 0),
                ];
                $statsError = 'Лимит Avito API по статистике (HTTP 429). Показаны последние сохранённые значения.';
            } else {
                throw $e;
            }
        }

        // Получаем расходы из Stats V2 API (не из операций)
        try {
            $spendingV2 = $this->getSpendingStatsV2($token, $userId, 7);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'HTTP 429')) {
                $spendingV2 = [
                    'spending_today' => (float) data_get($previousStats, 'spending_today', 0),
                    'presence_spending_today' => (float) data_get($previousStats, 'spending_presence_today', 0),
                    'promo_spending_today' => (float) data_get($previousStats, 'spending_promo_today', 0),
                    'avg_daily_spending' => (float) data_get($previousStats, 'spending_per_day', 0),
                    'period_days' => (int) data_get($previousStats, 'spending_period_days', 7),
                ];
                $spendingV2Error = 'Лимит Avito API по статистике расходов (stats/v2, HTTP 429). Показаны последние сохранённые значения.';
            } else {
                throw $e;
            }
        }

        $viewsCount = (int) data_get($todayStats, 'views', 0);
        $contactsCount = (int) data_get($todayStats, 'contacts', 0);
        $ctrValue = $viewsCount > 0 ? round(($contactsCount / $viewsCount) * 100, 2) : 0;

        $spendingToday = (float) data_get($spendingV2, 'spending_today', 0);

        $costPerContact = 0.0;
        if ($contactsCount > 0) {
            $costPerContact = $spendingToday / $contactsCount;
        }

        $real = (float) data_get($balance, 'real', 0);
        $bonus = (float) data_get($balance, 'bonus', 0);
        $cpaBalanceValue = $this->normalizeMoneyFromKopecks(data_get($cpaBalance, 'result.balance'));
        $cpaAdvancePeriod = $this->normalizeMoneyFromKopecks(data_get($cpaBalance, 'result.advance'));

        $advanceValue = $cpaBalanceValue ?? $cpaAdvancePeriod ?? $bonus;

        $combinedError = implode(' ', array_filter([$statsError, $spendingV2Error])) ?: null;

        $stats = [
            // Сумма на аккаунте = реальный баланс + аванс(бонус)
            'wallet' => round($real, 2),
            'sum_on_account' => round($real + $advanceValue, 2),
            'advance' => round($advanceValue, 2),
            'advance_source' => ($cpaBalanceValue !== null || $cpaAdvancePeriod !== null) ? 'cpa_balance_v2' : 'user_balance_bonus',
            'advance_period' => $cpaAdvancePeriod,
            'views_today' => $viewsCount,
            'contacts_today' => $contactsCount,
            'ctr' => $ctrValue,
            // Расходы из Stats V2 API (не из операций)
            'spending_today' => round($spendingToday, 2),
            'spending_presence_today' => round((float) data_get($spendingV2, 'presence_spending_today', 0), 2),
            'spending_promo_today' => round((float) data_get($spendingV2, 'promo_spending_today', 0), 2),
            'spending_source' => 'stats_v2',
            // Средние расходы за период (из Stats V2)
            'spending_per_day' => round((float) data_get($spendingV2, 'avg_daily_spending', 0), 2),
            'spending_period_days' => (int) data_get($spendingV2, 'period_days', 7),
            'cost_per_contact' => round($costPerContact, 2),
            'operations' => $operations,
            'synced_at' => now()->toDateTimeString(),
            'error' => $combinedError,
        ];

        return [
            'oauth_data' => $oauth,
            'profile_data' => [
                'id' => $userId,
                'name' => data_get($profile, 'name'),
                'email' => data_get($profile, 'email'),
                'phone' => data_get($profile, 'phone'),
                'profile_url' => data_get($profile, 'profile_url'),
            ],
            'stats_data' => $stats,
        ];
    }

    public function getProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get($this->baseUrl().'/core/v1/accounts/self');

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка получения профиля Avito: HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }

    public function getBalance(string $accessToken, int $userId): array
    {
        $response = Http::withToken($accessToken)
            ->get($this->baseUrl()."/core/v1/accounts/{$userId}/balance/");

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка получения баланса Avito: HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }

    public function getAccountHierarchy(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($this->baseUrl().'/checkAhUserV1');

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка проверки иерархии аккаунта Avito: HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }

    public function getCpaBalanceInfo(string $accessToken): ?array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->withHeaders([
                'X-Source' => (string) config('avito.cpa_source', 'crm_sumnikoff'),
            ])
            ->post($this->baseUrl().'/cpa/v2/balanceInfo', (object) []);

        if ($response->status() === 404 || $response->status() === 403) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        return $response->json() ?? null;
    }

    public function getTodayStats(string $accessToken, int $userId): array
    {
        $today = now()->format('Y-m-d');

        $itemIds = $this->getActiveItemIds($accessToken);
        if (empty($itemIds)) {
            return [
                'views' => 0,
                'contacts' => 0,
            ];
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post($this->baseUrl()."/stats/v1/accounts/{$userId}/items", [
                'dateFrom' => $today,
                'dateTo' => $today,
                'fields' => ['views', 'contacts', 'uniqViews', 'uniqContacts'],
                'itemIds' => $itemIds,
                'periodGrouping' => 'day',
            ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка получения статистики Avito: HTTP '.$response->status());
        }

        $payload = $response->json() ?? [];
        $items = (array) data_get($payload, 'result.items', []);

        $views = 0;
        $contacts = 0;

        foreach ($items as $item) {
            foreach ((array) data_get($item, 'stats', []) as $row) {
                if ((string) data_get($row, 'date') !== $today) {
                    continue;
                }

                $views += (int) (data_get($row, 'uniqViews') ?? data_get($row, 'views', 0));
                $contacts += (int) (data_get($row, 'uniqContacts') ?? data_get($row, 'contacts', 0));
            }
        }

        return [
            'views' => $views,
            'contacts' => $contacts,
        ];
    }

    /**
     * Получение статистики расходов через Stats V2 API.
     *
     * Метрики приходят в копейках, конвертируем в рубли.
     * Лимит: 1 запрос в минуту на этот endpoint.
     *
     * @param  int  $periodDays  Количество дней периода (включая сегодня)
     */
    public function getSpendingStatsV2(string $accessToken, int $userId, int $periodDays = 7): array
    {
        $today = now()->format('Y-m-d');
        $dateFrom = now()->subDays(max(0, $periodDays - 1))->format('Y-m-d');

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl()."/stats/v2/accounts/{$userId}/items", [
                'dateFrom' => $dateFrom,
                'dateTo' => $today,
                'grouping' => 'day',
                'metrics' => ['presenceSpending', 'promoSpending', 'spending'],
                'limit' => 1000,
                'offset' => 0,
            ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка получения статистики расходов Avito (stats/v2): HTTP '.$response->status());
        }

        $payload = $response->json() ?? [];
        $groupings = (array) data_get($payload, 'result.groupings', []);

        // Ответ Stats V2: groupings — массив объектов:
        //   { id: <unix_timestamp_utc>, type: "dates", metrics: [{slug: "spending", value: <kopecks>}, ...] }
        // ВАЖНО: id — это UTC-полночь, поэтому сравниваем по дате (Y-m-d), а не по timestamp.
        $todayDate = now()->format('Y-m-d');

        $todaySpending = 0.0;
        $todayPresence = 0.0;
        $todayPromo = 0.0;
        $totalSpending = 0.0;

        foreach ($groupings as $entry) {
            $entryTimestamp = (int) data_get($entry, 'id', 0);
            $entryDate = Carbon::createFromTimestamp($entryTimestamp, 'UTC')->format('Y-m-d');
            $metrics = (array) data_get($entry, 'metrics', []);

            // Парсим массив метрик [{slug, value}, ...] в плоский массив slug => value
            $metricsMap = [];
            foreach ($metrics as $metric) {
                $slug = (string) data_get($metric, 'slug', '');
                if ($slug !== '') {
                    $metricsMap[$slug] = (float) data_get($metric, 'value', 0);
                }
            }

            $spending = ($metricsMap['spending'] ?? 0) / 100;       // копейки → рубли
            $presenceSpending = ($metricsMap['presenceSpending'] ?? 0) / 100;
            $promoSpending = ($metricsMap['promoSpending'] ?? 0) / 100;

            $totalSpending += $spending;

            if ($entryDate === $todayDate) {
                $todaySpending = $spending;
                $todayPresence = $presenceSpending;
                $todayPromo = $promoSpending;
            }
        }

        $avgDailySpending = $periodDays > 0 ? round($totalSpending / $periodDays, 2) : 0.0;

        return [
            'spending_today' => round($todaySpending, 2),
            'presence_spending_today' => round($todayPresence, 2),
            'promo_spending_today' => round($todayPromo, 2),
            'avg_daily_spending' => $avgDailySpending,
            'period_days' => $periodDays,
        ];
    }

    public function getOperationsHistory(string $accessToken, Carbon $from, Carbon $to): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post($this->baseUrl().'/core/v1/accounts/operations_history/', [
                'dateTimeFrom' => $from->copy()->startOfDay()->format('Y-m-d\TH:i:s'),
                'dateTimeTo' => $to->copy()->endOfDay()->format('Y-m-d\TH:i:s'),
            ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка получения истории операций Avito: HTTP '.$response->status());
        }

        $operations = (array) data_get($response->json() ?? [], 'result.operations', []);

        return collect($operations)
            ->map(function (array $operation) {
                return [
                    'updated_at' => data_get($operation, 'updatedAt'),
                    'operation_name' => data_get($operation, 'operationName'),
                    'operation_type' => data_get($operation, 'operationType'),
                    'service_name' => data_get($operation, 'serviceName'),
                    'service_type' => data_get($operation, 'serviceType'),
                    'amount_rub' => (float) data_get($operation, 'amountRub', 0),
                    'amount_bonus' => (float) data_get($operation, 'amountBonus', 0),
                    'amount_total' => (float) data_get($operation, 'amountTotal', 0),
                    'item_id' => data_get($operation, 'itemId'),
                ];
            })
            ->sortByDesc('updated_at')
            ->values()
            ->take(30)
            ->all();
    }

    protected function getActiveItemIds(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($this->baseUrl().'/core/v1/items', [
                'per_page' => 100,
                'page' => 1,
                'status' => 'active',
            ]);

        if (! $response->ok()) {
            throw new \RuntimeException('Ошибка получения объявлений Avito: HTTP '.$response->status());
        }

        $resources = (array) data_get($response->json() ?? [], 'resources', []);
        $itemIds = [];
        foreach ($resources as $resource) {
            $itemId = data_get($resource, 'id');
            if ($itemId) {
                $itemIds[] = (int) $itemId;
            }
        }

        return array_values(array_unique($itemIds));
    }

    protected function tokenExpired(array $oauthData): bool
    {
        $expiresAt = data_get($oauthData, 'expires_at');
        if (! $expiresAt) {
            return true;
        }

        try {
            return Carbon::parse($expiresAt)->lte(now()->addMinute());
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('avito.base_url', 'https://api.avito.ru'), '/');
    }

    protected function resolveBalance(string $accessToken, int $userId): array
    {
        $balance = $this->getBalance($accessToken, $userId);

        $real = (float) data_get($balance, 'real', 0);
        $bonus = (float) data_get($balance, 'bonus', 0);
        if (($real + $bonus) > 0) {
            return $balance;
        }

        try {
            $hierarchy = $this->getAccountHierarchy($accessToken);
            $companyId = (int) (data_get($hierarchy, 'result.avitoCompanyId') ?? data_get($hierarchy, 'avitoCompanyId'));

            if ($companyId > 0 && $companyId !== $userId) {
                $companyBalance = $this->getBalance($accessToken, $companyId);
                $companyReal = (float) data_get($companyBalance, 'real', 0);
                $companyBonus = (float) data_get($companyBalance, 'bonus', 0);

                if (($companyReal + $companyBonus) > 0) {
                    return $companyBalance;
                }
            }
        } catch (\Throwable $e) {
            // fallback silently to user-level balance
        }

        return $balance;
    }

    protected function normalizeMoneyFromKopecks(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return round(((float) $value) / 100, 2);
    }

    protected function calculateTodaySpendingBreakdown(array $operations): array
    {
        $todayDate = now()->format('Y-m-d');
        $totals = [
            'placement' => 0.0,
            'views' => 0.0,
            'other' => 0.0,
            'total' => 0.0,
        ];

        foreach ($operations as $operation) {
            $opDate = (string) data_get($operation, 'updated_at', '');
            if ($opDate === '' || mb_substr($opDate, 0, 10) !== $todayDate) {
                continue;
            }

            $amount = $this->resolveOperationAmount($operation);
            if ($amount <= 0) {
                continue;
            }

            $opType = mb_strtolower((string) data_get($operation, 'operation_type', ''));
            $opName = mb_strtolower((string) data_get($operation, 'operation_name', ''));

            if ($this->isCreditOperation($opType, $opName)) {
                continue;
            }

            $bucket = $this->detectSpendingBucket($operation);
            $totals[$bucket] += $amount;
            $totals['total'] += $amount;
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        return $totals;
    }

    protected function resolveOperationAmount(array $operation): float
    {
        $total = (float) data_get($operation, 'amount_total', 0);
        if ($total > 0) {
            return $total;
        }

        $rub = (float) data_get($operation, 'amount_rub', 0);
        $bonus = (float) data_get($operation, 'amount_bonus', 0);

        return max(0, $rub + $bonus);
    }

    protected function isCreditOperation(string $opType, string $opName): bool
    {
        foreach (['внесен', 'внесение', 'пополн', 'аванс', 'сторно', 'возврат'] as $keyword) {
            if (str_contains($opType, $keyword) || str_contains($opName, $keyword)) {
                return true;
            }
        }

        return false;
    }

    protected function detectSpendingBucket(array $operation): string
    {
        $serviceType = mb_strtolower((string) data_get($operation, 'service_type', ''));
        $serviceName = mb_strtolower((string) data_get($operation, 'service_name', ''));
        $opName = mb_strtolower((string) data_get($operation, 'operation_name', ''));
        $opType = mb_strtolower((string) data_get($operation, 'operation_type', ''));

        $haystack = implode(' ', [$serviceType, $serviceName, $opName, $opType]);

        if (
            str_contains($haystack, 'публикац') ||
            str_contains($haystack, 'размещ') ||
            str_contains($haystack, 'bbl')
        ) {
            return 'placement';
        }

        if (
            str_contains($haystack, 'целев') ||
            str_contains($haystack, 'просмотр') ||
            str_contains($haystack, 'клик') ||
            str_contains($haystack, 'действ') ||
            str_contains($haystack, 'cpa') ||
            str_contains($haystack, 'cpx')
        ) {
            return 'views';
        }

        return 'other';
    }

    protected function calculateAverageDailySpending(array $operations, int $days = 7): float
    {
        if ($days <= 0) {
            return 0.0;
        }

        $today = Carbon::today();
        $total = 0.0;

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->subDays($i)->format('Y-m-d');

            foreach ($operations as $operation) {
                $opDate = (string) data_get($operation, 'updated_at', '');
                if ($opDate === '' || mb_substr($opDate, 0, 10) !== $date) {
                    continue;
                }

                $amount = $this->resolveOperationAmount($operation);
                if ($amount <= 0) {
                    continue;
                }

                $opType = mb_strtolower((string) data_get($operation, 'operation_type', ''));
                $opName = mb_strtolower((string) data_get($operation, 'operation_name', ''));

                if ($this->isCreditOperation($opType, $opName)) {
                    continue;
                }

                $total += $amount;
            }
        }

        return $days > 0 ? round($total / $days, 2) : 0.0;
    }
}

