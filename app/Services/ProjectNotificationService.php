<?php

namespace App\Services;

use App\Models\Importance;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Collection;

class ProjectNotificationService
{
    public function notifyProjectCreated(Project $project, ?int $actorId = null): void
    {
        if ($project->marketer_id) {
            $this->sendToUsers(
                User::query()->whereKey($project->marketer_id)->get(),
                [
                    'actor_id' => $actorId,
                    'project_id' => $project->id,
                    'type' => 'project.assignment.assigned',
                    'title' => 'Назначение на проект',
                    'message' => "Вы назначены ответственным за проект «{$project->title}».",
                    'data' => ['event' => 'project_created_assignment'],
                ]
            );
        }

        $adminsAndManagers = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_PROJECT_MANAGER])
            ->get();

        $this->sendToUsers(
            $adminsAndManagers,
            [
                'actor_id' => $actorId,
                'project_id' => $project->id,
                'type' => 'project.created',
                'title' => 'Создан новый проект',
                'message' => "Создан проект «{$project->title}».",
                'data' => ['event' => 'project_created'],
            ],
            targetRole: null,
            targetPosition: null
        );
    }

    public function notifyMarketerChanged(Project $project, ?int $oldMarketerId, ?int $newMarketerId, ?int $actorId = null): void
    {
        if ($oldMarketerId && $oldMarketerId !== $newMarketerId) {
            $this->sendToUsers(
                User::query()->whereKey($oldMarketerId)->get(),
                [
                    'actor_id' => $actorId,
                    'project_id' => $project->id,
                    'type' => 'project.assignment.removed',
                    'title' => 'Снятие с проекта',
                    'message' => "Вы сняты с проекта «{$project->title}».",
                    'data' => ['event' => 'project_marketer_changed', 'old_marketer_id' => $oldMarketerId, 'new_marketer_id' => $newMarketerId],
                ]
            );
        }

        if ($newMarketerId && $newMarketerId !== $oldMarketerId) {
            $this->sendToUsers(
                User::query()->whereKey($newMarketerId)->get(),
                [
                    'actor_id' => $actorId,
                    'project_id' => $project->id,
                    'type' => 'project.assignment.assigned',
                    'title' => 'Назначение на проект',
                    'message' => "Вы назначены ответственным за проект «{$project->title}».",
                    'data' => ['event' => 'project_marketer_changed', 'old_marketer_id' => $oldMarketerId, 'new_marketer_id' => $newMarketerId],
                ]
            );
        }
    }

    public function notifyProjectUpdated(Project $project, array $changes, ?int $actorId = null): void
    {
        if (empty($changes)) {
            return;
        }

        // 1) Отправка маркетологу — исключаем поля, которые ему не нужны
        $excludeForMarketer = ['payment_type', 'contract_amount'];
        $changesForMarketer = array_values(array_filter($changes, fn ($row) => ! in_array($row['field'], $excludeForMarketer, true)));

        if (! empty($changesForMarketer) && $project->marketer_id) {
            $changesText = collect($changesForMarketer)
                ->map(fn ($row) => "• {$row['label']}: {$row['old']} → {$row['new']}")
                ->implode("\n");

            $message = "Изменён проект «{$project->title}».\n{$changesText}";

            $this->sendToUsers(
                User::query()->whereKey($project->marketer_id)->get(),
                [
                    'actor_id' => $actorId,
                    'project_id' => $project->id,
                    'type' => 'project.updated',
                    'title' => 'Изменения в проекте',
                    'message' => $message,
                    'data' => ['event' => 'project_updated', 'changes' => $changesForMarketer],
                ]
            );
        }

        // 2) Если изменились тип оплаты или сумма — уведомляем всех admin + project_manager
        $adminRelevant = ['payment_type', 'contract_amount'];
        $changesForAdmins = array_values(array_filter($changes, fn ($row) => in_array($row['field'], $adminRelevant, true)));

        if (! empty($changesForAdmins)) {
            $changesTextAdmin = collect($changesForAdmins)
                ->map(fn ($row) => "• {$row['label']}: {$row['old']} → {$row['new']}")
                ->implode("\n");

            $messageAdmin = "Изменён проект «{$project->title}».\n{$changesTextAdmin}";

            $adminsAndPM = User::query()->whereIn('role', [User::ROLE_ADMIN, User::ROLE_PROJECT_MANAGER])->get();

            $this->sendToUsers(
                $adminsAndPM,
                [
                    'actor_id' => $actorId,
                    'project_id' => $project->id,
                    'type' => 'project.updated.finance',
                    'title' => 'Обновлены финансовые данные проекта',
                    'message' => $messageAdmin,
                    'data' => ['event' => 'project_financials_updated', 'changes' => $changesForAdmins],
                ]
            );
        }
    }

    public function sendByRoleOrPosition(array $payload, ?string $role = null, ?string $position = null): void
    {
        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        if ($position) {
            $query->where('position', $position);
        }

        $users = $query->get();

        $this->sendToUsers($users, $payload, $role, $position);
    }

    public function formatTrackedChanges(array $original, array $new): array
    {
        $labels = [
            'title' => 'Название',
            'organization_id' => 'Организация',
            'city' => 'Город',
            'importance_id' => 'Важность',
            'contract_amount' => 'Сумма договора',
            'contract_date' => 'Дата договора',
            'payment_method_id' => 'Тип оплаты',
            'payment_type' => 'Тип расчёта',
            'payment_due_day' => 'Срок оплаты (день)',
            'comment' => 'Комментарий',
            'status' => 'Статус',
            'closed_at' => 'Дата закрытия',
        ];

        $changes = [];

        foreach ($labels as $field => $label) {
            $oldValue = $original[$field] ?? null;
            $newValue = $new[$field] ?? null;

            // normalize values for reliable comparison
            $oldNorm = $this->normalizeFieldValue($field, $oldValue);
            $newNorm = $this->normalizeFieldValue($field, $newValue);

            if ($oldNorm === $newNorm) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $label,
                'old' => $this->humanizeValue($field, $oldValue),
                'new' => $this->humanizeValue($field, $newValue),
            ];
        }

        return $changes;
    }

    protected function normalizeFieldValue(string $field, $value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        switch ($field) {
            case 'contract_date':
            case 'closed_at':
                try {
                    return \Carbon\Carbon::parse($value)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return (string) $value;
                }

            case 'contract_amount':
                return number_format((float) $value, 2, '.', '');

            case 'organization_id':
            case 'importance_id':
            case 'payment_method_id':
            case 'payment_due_day':
                return is_null($value) ? null : (int) $value;

            default:
                return trim((string) $value);
        }
    }

    protected function humanizeValue(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($field) {
            'organization_id' => Organization::query()->whereKey($value)->value('name_short')
                ?? Organization::query()->whereKey($value)->value('name_full')
                ?? (string) $value,
            'importance_id' => Importance::query()->whereKey($value)->value('name') ?? (string) $value,
            'payment_method_id' => PaymentMethod::query()->whereKey($value)->value('title') ?? (string) $value,
            'contract_date', 'closed_at' => optional(\Carbon\Carbon::parse($value))->format('d.m.Y') ?: (string) $value,
            'payment_type' => match ((string) $value) {
                'paid' => 'Платят',
                'barter' => 'Бартер',
                'own' => 'Свои проекты',
                default => (string) $value,
            },
            'status' => match ((string) $value) {
                Project::STATUS_IN_PROGRESS => 'В работе',
                Project::STATUS_PAUSED => 'На паузе',
                Project::STATUS_STOPPED => 'Остановлен',
                default => (string) $value,
            },
            default => (string) $value,
        };
    }

    protected function sendToUsers(Collection $users, array $payload, ?string $targetRole = null, ?string $targetPosition = null): void
    {
        $rows = $users
            ->unique('id')
            ->map(function (User $user) use ($payload, $targetRole, $targetPosition) {
                return [
                    'user_id' => $user->id,
                    'actor_id' => $payload['actor_id'] ?? null,
                    'project_id' => $payload['project_id'] ?? null,
                    'type' => $payload['type'] ?? 'project.info',
                    'title' => $payload['title'] ?? 'Уведомление',
                    'message' => $payload['message'] ?? null,
                    'data' => isset($payload['data']) ? json_encode($payload['data'], JSON_UNESCAPED_UNICODE) : null,
                    'target_role' => $targetRole,
                    'target_position' => $targetPosition,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            ->values()
            ->all();

        if (! empty($rows)) {
            UserNotification::query()->insert($rows);
        }
    }
}
