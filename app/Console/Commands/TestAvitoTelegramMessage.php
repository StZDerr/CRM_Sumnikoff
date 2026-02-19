<?php

namespace App\Console\Commands;

use App\Services\AvitoNotificationService;
use Illuminate\Console\Command;

class TestAvitoTelegramMessage extends Command
{
    protected $signature = 'avito:test-telegram {message? : Текст тестового сообщения}';

    protected $description = 'Send test Telegram message to Avito group chat';

    public function handle(AvitoNotificationService $avitoNotificationService): int
    {
        $result = $avitoNotificationService->sendTestTelegramMessage($this->argument('message'));

        if (! (bool) data_get($result, 'sent', false)) {
            $error = (string) data_get($result, 'error', 'Неизвестная ошибка');
            $status = data_get($result, 'status');

            $this->error('Сообщение не отправлено: '.$error);
            if ($status !== null) {
                $this->line('HTTP статус: '.$status);
            }

            return self::FAILURE;
        }

        $this->info('Тестовое сообщение успешно отправлено в Telegram.');

        return self::SUCCESS;
    }
}
