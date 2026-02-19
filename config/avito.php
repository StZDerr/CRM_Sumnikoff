<?php

return [
    'base_url' => env('AVITO_API_BASE_URL', 'https://api.avito.ru'),
    'cpa_source' => env('AVITO_CPA_SOURCE', 'crm_sumnikoff'),
    'telegram' => [
        'bot_token' => env('AVITO_TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('AVITO_TELEGRAM_CHAT_ID', '-1002623359989'),
        'message_thread_id' => env('AVITO_TELEGRAM_MESSAGE_THREAD_ID'),
    ],
];
