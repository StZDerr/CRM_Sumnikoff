<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonthlyExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'day_of_month',
        'title',
        'amount',
        'note',
        'is_active',
    ];

    protected $casts = [
        'day_of_month' => 'integer',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(MonthlyExpenseStatus::class);
    }

    /**
     * Отметить расход как оплаченный за месяц (YYYY-MM) и создать Expense.
     *
     * @throws ValidationException
     */
    public function markAsPaid(string $month): Expense
    {
        // preserve old behaviour but delegate to the new flexible creator
        return $this->createExpenseForMonth($month, []);
    }

    /**
     * Создаёт Expense для указанного месяца, учитывая переданные переопределения полей.
     * Переопределения могут содержать: amount, expense_date (Y-m-d), description,
     * expense_category_id, project_id, organization_id
     *
     * @throws ValidationException
     */
    public function createExpenseForMonth(string $month, array $overrides = []): Expense
    {
        $monthKey = $this->normalizeMonth($month);

        return DB::transaction(function () use ($monthKey, $overrides) {
            $status = $this->statuses()->where('month', $monthKey)->lockForUpdate()->first();

            if ($status && $status->paid_at) {
                throw ValidationException::withMessages([
                    'month' => 'Этот ежемесячный расход уже оплачен за выбранный месяц.',
                ]);
            }

            if (! $status) {
                $status = $this->statuses()->create([
                    'month' => $monthKey,
                ]);
            }

            // amount: override or default
            $amount = array_key_exists('amount', $overrides) ? $overrides['amount'] : $this->amount;

            // expense date: override or computed due date
            if (! empty($overrides['expense_date'])) {
                try {
                    $expenseDate = Carbon::createFromFormat('Y-m-d', $overrides['expense_date'])->startOfDay();
                } catch (\Throwable $e) {
                    throw ValidationException::withMessages([
                        'expense_date' => 'Некорректная дата расхода. Ожидается формат YYYY-MM-DD.',
                    ]);
                }
            } else {
                $expenseDate = $this->buildExpenseDate($monthKey);
            }

            // description: override or title + note
            $description = $overrides['description'] ?? $this->title;
            if (empty($overrides['description']) && ! empty($this->note)) {
                $description .= ' — '.$this->note;
            }

            // expense category: override or office default
            $expenseCategoryId = $overrides['expense_category_id'] ?? null;
            if (empty($expenseCategoryId)) {
                $officeCategory = ExpenseCategory::office()
                    ->where('is_salary', false)
                    ->orderBy('sort_order')
                    ->first();

                if (! $officeCategory) {
                    throw ValidationException::withMessages([
                        'expense_category_id' => 'Не найдена офисная категория расходов. Создайте категорию с признаком "Офис".',
                    ]);
                }

                $expenseCategoryId = $officeCategory->id;
            }

            $expense = Expense::create([
                'expense_date' => $expenseDate,
                'amount' => $amount,
                'expense_category_id' => $expenseCategoryId,
                'project_id' => $overrides['project_id'] ?? null,
                'organization_id' => $overrides['organization_id'] ?? null,
                'status' => 'paid',
                'description' => $description,
            ]);

            $status->update([
                'paid_at' => now(),
                'expense_id' => $expense->id,
            ]);

            return $expense;
        });
    }

    protected function normalizeMonth(string $month): string
    {
        try {
            return Carbon::createFromFormat('Y-m', $month)->format('Y-m');
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'month' => 'Некорректный месяц. Ожидается формат YYYY-MM.',
            ]);
        }
    }

    protected function buildExpenseDate(string $monthKey): Carbon
    {
        $base = Carbon::createFromFormat('Y-m', $monthKey)->startOfMonth();
        $day = min(max((int) $this->day_of_month, 1), $base->daysInMonth);

        return $base->copy()->day($day)->startOfDay();
    }
}
