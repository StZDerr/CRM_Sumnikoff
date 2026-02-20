<?php

namespace App\Observers;

use App\Models\BeelineCallRecord;
use App\Models\KanbanColumn;
use App\Models\PhoneLead;
use Illuminate\Support\Carbon;

class BeelineCallRecordObserver
{
    public function created(BeelineCallRecord $record): void
    {
        $phone = trim((string) $record->phone);
        if ($phone === '') {
            return;
        }

        $newColumn = KanbanColumn::query()->firstOrCreate(
            ['name' => 'Новые'],
            ['sort_order' => 0]
        );

        if ((int) $newColumn->sort_order !== 0) {
            $newColumn->sort_order = 0;
            $newColumn->save();
        }

        $lead = PhoneLead::query()->firstOrCreate(
            ['phone' => $phone],
            [
                'kanban_column_id' => $newColumn->id,
                'sort_order' => (int) PhoneLead::query()->where('kanban_column_id', $newColumn->id)->max('sort_order') + 1,
                'last_call_at' => $record->call_date,
                'calls_count' => 0,
            ]
        );

        $callDate = $record->call_date ? Carbon::parse($record->call_date) : null;
        $currentLast = $lead->last_call_at ? Carbon::parse($lead->last_call_at) : null;

        $lead->calls_count = ((int) $lead->calls_count) + 1;
        if ($callDate && (! $currentLast || $callDate->greaterThan($currentLast))) {
            $lead->last_call_at = $callDate;
        }
        $lead->save();

        if ((int) $record->phone_lead_id !== (int) $lead->id) {
            $record->phone_lead_id = $lead->id;
            $record->saveQuietly();
        }
    }
}
