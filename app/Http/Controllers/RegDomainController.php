<?php

namespace App\Http\Controllers;

use App\Services\RegApiClient;
use Illuminate\View\View;

class RegDomainController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(): View
    {
        $domains = [];
        $error = null;
        $priceError = null;
        $renewPrices = [];
        $currency = 'RUR';

        $username = config('regapi.username');
        $password = config('regapi.password');

        if (empty($username) || empty($password)) {
            $error = 'Не заполнены REG_API_USERNAME / REG_API_PASSWORD в .env.';
        } else {
            try {
                $client = RegApiClient::make();
                $domains = $client->getDomains();
                $domains = collect($domains)
                    ->filter(fn ($item) => ! empty($item['dname']))
                    ->sortBy('expiration_date')
                    ->values()
                    ->all();

                try {
                    $pricesData = $client->getDomainPrices();
                    $currency = $pricesData['currency'] ?? $currency;
                    $prices = $pricesData['prices'] ?? [];

                    foreach ($prices as $tld => $priceData) {
                        if (isset($priceData['renew_price'])) {
                            $renewPrices[$tld] = $priceData['renew_price'];
                        }
                    }
                } catch (\Throwable $e) {
                    $priceError = $e->getMessage();
                }
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return view('admin.domains.index', compact('domains', 'error', 'priceError', 'renewPrices', 'currency'));
    }
}
