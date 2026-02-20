<?php

namespace App\Console\Commands;

use App\Models\BeelineCallRecord;
use App\Models\KanbanColumn;
use App\Models\PhoneLead;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackfillPhoneLeadsFromCalls extends Command
{
    protected $signature = 'leads:backfill-from-calls';

    protected $description = 'Создать/обновить phone_leads из существующих beeline_call_records и привязать phone_lead_id';

    public function handle(): int
    {
        $this->info('Запуск бэкфилла лидов из звонков...');

        $newColumn = KanbanColumn::query()->firstOrCreate(
            ['name' => 'Новые'],
            ['sort_order' => 0]
        );

        if ((int) $newColumn->sort_order !== 0) {
            $newColumn->sort_order = 0;
            $newColumn->save();
        }

        $phoneStats = BeelineCallRecord::query()
            ->selectRaw('phone, MAX(call_date) as last_call_at, COUNT(*) as calls_count')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->get();

        $createdLeads = 0;
        $updatedLeads = 0;
        $linkedCalls = 0;
        $maxOrderInNew = (int) PhoneLead::query()
            ->where('kanban_column_id', $newColumn->id)
            ->max('sort_order');

        foreach ($phoneStats as $stat) {
            $phone = trim((string) $stat->phone);
            if ($phone === '') {
                continue;
            }

            $lead = PhoneLead::query()->where('phone', $phone)->first();
            $isNew = false;

            if (! $lead) {
                $maxOrderInNew++;
                $lead = new PhoneLead();
                $lead->phone = $phone;
                $lead->kanban_column_id = $newColumn->id;
                $lead->sort_order = $maxOrderInNew;
                $isNew = true;
            }

            if (! $lead->kanban_column_id) {
                $maxOrderInNew++;
                $lead->kanban_column_id = $newColumn->id;
                $lead->sort_order = $lead->sort_order ?: $maxOrderInNew;
            }

            $statLastCall = $stat->last_call_at ? Carbon::parse($stat->last_call_at) : null;
            $lead->last_call_at = $statLastCall;
            $lead->calls_count = (int) $stat->calls_count;
            $lead->save();

            if ($isNew) {
                $createdLeads++;
            } else {
                $updatedLeads++;
            }

            $linkedCalls += BeelineCallRecord::query()
                ->where('phone', $phone)
                ->where(function ($query) use ($lead) {
                    $query->whereNull('phone_lead_id')
                        ->orWhere('phone_lead_id', '!=', $lead->id);
                })
                ->update(['phone_lead_id' => $lead->id]);
        }

        $this->info('Бэкфилл завершён.');
        $this->line('Создано лидов: '.$createdLeads);
        $this->line('Обновлено лидов: '.$updatedLeads);
        $this->line('Привязано звонков: '.$linkedCalls);

        return self::SUCCESS;
    }
}
