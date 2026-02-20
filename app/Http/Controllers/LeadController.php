<?php

namespace App\Http\Controllers;

use App\Models\CampaignSource;
use App\Models\KanbanColumn;
use App\Models\LeadTopic;
use App\Models\PhoneLead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index()
    {
        $newColumn = KanbanColumn::query()->firstOrCreate(
            ['name' => 'Новые'],
            ['sort_order' => 0]
        );

        if ((int) $newColumn->sort_order !== 0) {
            $newColumn->sort_order = 0;
            $newColumn->save();
        }

        $maxOrderInNew = (int) PhoneLead::query()
            ->where('kanban_column_id', $newColumn->id)
            ->max('sort_order');

        $unassignedLeads = PhoneLead::query()
            ->whereNull('kanban_column_id')
            ->orderBy('id')
            ->get(['id']);

        foreach ($unassignedLeads as $lead) {
            $maxOrderInNew++;
            PhoneLead::query()
                ->where('id', $lead->id)
                ->update([
                    'kanban_column_id' => $newColumn->id,
                    'sort_order' => $maxOrderInNew,
                ]);
        }

        $columns = KanbanColumn::query()
            ->orderByRaw("CASE WHEN name = 'Новые' THEN 0 ELSE 1 END")
            ->orderBy('sort_order')
            ->with([
                'phoneLeads' => function ($query) {
                    $query->orderBy('sort_order')
                        ->with([
                            'latestCall:beeline_call_records.id,beeline_call_records.phone_lead_id,beeline_call_records.direction,beeline_call_records.call_date',
                            'topic:id,name',
                            'campaignSource:id,name',
                            'responsibleUser:id,name',
                        ]);
                },
            ])
            ->get();

        $topics = LeadTopic::query()->ordered()->get(['id', 'name']);
        $campaignSources = CampaignSource::query()->forLeads()->ordered()->get(['id', 'name']);
        $responsibleUsers = User::query()->orderBy('name')->get(['id', 'name']);

        //_regions relative to MSK, list of offsets from -12 to +14 hours_
        $regions = [];
        for ($i = -12; $i <= 14; $i++) {
            $prefix = $i >= 0 ? '+' : '';
            $regions[$i] = sprintf('UTC%s%d', $prefix, $i);
        }

        return view('admin.bilain.lead.index', compact('columns', 'topics', 'campaignSources', 'responsibleUsers', 'regions'));
    }

    public function storeQuickLead(Request $request): JsonResponse
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\d{10}$/', 'unique:phone_leads,phone'],
            'region' => ['nullable', 'string', 'max:255'],
            'lead_topic_id' => ['nullable', 'integer', 'exists:lead_topics,id'],
        ], [
            'phone.required' => 'Телефон обязателен.',
            'phone.regex' => 'Телефон должен содержать 10 цифр (без кода страны).',
            'phone.unique' => 'Такой номер уже использовался.',
        ]);

        $newColumn = KanbanColumn::query()->firstOrCreate(
            ['name' => 'Новые'],
            ['sort_order' => 0]
        );

        $maxSort = (int) PhoneLead::query()
            ->where('kanban_column_id', $newColumn->id)
            ->max('sort_order');

        $lead = PhoneLead::query()->create([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'],
            'region' => $data['region'] ?? null,
            'lead_topic_id' => $data['lead_topic_id'] ?? null,
            'kanban_column_id' => $newColumn->id,
            'sort_order' => $maxSort + 1,
        ]);

        $lead->load(['topic:id,name']);

        return response()->json([
            'ok' => true,
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'region' => $lead->region,
                'topic' => $lead->topic?->name,
                'column_id' => $lead->kanban_column_id,
                'sort_order' => $lead->sort_order,
            ],
        ], 201);
    }

    public function show(PhoneLead $phoneLead): JsonResponse
    {
        $phoneLead->load([
            'topic:id,name',
            'campaignSource:id,name',
            'responsibleUser:id,name',
        ]);

        return response()->json([
            'ok' => true,
            'lead' => [
                'id' => $phoneLead->id,
                'name' => $phoneLead->name,
                'phone' => $phoneLead->phone,
                'region' => $phoneLead->region,
                'lead_topic_id' => $phoneLead->lead_topic_id,
                'note' => $phoneLead->note,
                'comment' => $phoneLead->comment,
                'deadline_at' => optional($phoneLead->deadline_at)->format('Y-m-d\\TH:i'),
                'amount' => $phoneLead->amount,
                'deal_start_date' => optional($phoneLead->deal_start_date)->format('Y-m-d'),
                'campaign_source_id' => $phoneLead->campaign_source_id,
                'responsible_user_id' => $phoneLead->responsible_user_id,
            ],
        ]);
    }

    public function update(Request $request, PhoneLead $phoneLead): JsonResponse
    {
        $request->merge([
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\d{10}$/', Rule::unique('phone_leads', 'phone')->ignore($phoneLead->id)],
            'region' => ['nullable', 'string', 'max:255'],
            'lead_topic_id' => ['nullable', 'integer', 'exists:lead_topics,id'],
            'note' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
            'deadline_at' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'deal_start_date' => ['nullable', 'date'],
            'campaign_source_id' => [
                'nullable',
                'integer',
                Rule::exists('campaign_sources', 'id')->where(fn ($q) => $q->where('is_lead_source', 1)),
            ],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ], [
            'phone.required' => 'Телефон обязателен.',
            'phone.regex' => 'Телефон должен содержать 10 цифр (без кода страны).',
            'phone.unique' => 'Такой номер уже использовался.',
        ]);

        $phoneLead->update($data);

        return response()->json(['ok' => true]);
    }

    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) === 11 && ($digits[0] === '7' || $digits[0] === '8')) {
            $digits = substr($digits, 1);
        }

        return substr($digits, 0, 10);
    }

    public function storeTopic(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:lead_topics,name'],
        ]);

        $topic = LeadTopic::query()->create([
            'name' => trim($data['name']),
        ]);

        return response()->json([
            'ok' => true,
            'topic' => [
                'id' => $topic->id,
                'name' => $topic->name,
            ],
        ], 201);
    }

    public function storeCampaignSource(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($data['name']);
        $base = Str::slug($name);
        $slug = $base ?: Str::random(6);
        $index = 1;

        while (CampaignSource::query()->where('slug', $slug)->exists()) {
            $slug = ($base ?: 'source').'-'.$index;
            $index++;
        }

        $source = CampaignSource::query()->create([
            'name' => $name,
            'slug' => $slug,
            'is_lead_source' => true,
        ]);

        return response()->json([
            'ok' => true,
            'source' => [
                'id' => $source->id,
                'name' => $source->name,
            ],
        ], 201);
    }

    public function storeColumn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:kanban_columns,name'],
        ]);

        $column = KanbanColumn::query()->create([
            'name' => $data['name'],
            'sort_order' => ((int) KanbanColumn::query()->max('sort_order')) + 1,
        ]);

        return response()->json([
            'ok' => true,
            'column' => [
                'id' => $column->id,
                'name' => $column->name,
                'sort_order' => $column->sort_order,
            ],
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'columns' => ['required', 'array'],
            'columns.*.id' => ['required', 'integer', 'exists:kanban_columns,id'],
            'columns.*.lead_ids' => ['required', 'array'],
            'columns.*.lead_ids.*' => ['integer', 'exists:phone_leads,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['columns'] as $columnPayload) {
                $columnId = (int) $columnPayload['id'];
                $leadIds = array_values(array_map('intval', $columnPayload['lead_ids']));

                PhoneLead::query()
                    ->whereIn('id', $leadIds)
                    ->update(['kanban_column_id' => $columnId]);

                foreach ($leadIds as $index => $leadId) {
                    PhoneLead::query()
                        ->where('id', $leadId)
                        ->update(['sort_order' => $index + 1]);
                }
            }
        });

        return response()->json(['ok' => true]);
    }
}
