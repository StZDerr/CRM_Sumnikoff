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
        $monthKey = $this->normalizeMonth($month);

        return DB::transaction(function () use ($monthKey) {
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

            $expenseDate = $this->buildExpenseDate($monthKey);
            $description = $this->title;
            if (! empty($this->note)) {
                $description .= ' — '.$this->note;
            }

            $officeCategory = ExpenseCategory::office()
                ->where('is_salary', false)
                ->orderBy('sort_order')
                ->first();

            if (! $officeCategory) {
                throw ValidationException::withMessages([
                    'expense_category_id' => 'Не найдена офисная категория расходов. Создайте категорию с признаком "Офис".',
                ]);
            }

            $expense = Expense::create([
                'expense_date' => $expenseDate,
                'amount' => $this->amount,
                'expense_category_id' => $officeCategory->id,
                'project_id' => null,
                'organization_id' => null,
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
