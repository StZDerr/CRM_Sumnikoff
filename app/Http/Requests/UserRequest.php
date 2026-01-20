<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('users', 'login')->ignore($userId),
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'birth_date' => ['nullable', 'date'],
            'password' => $this->isMethod('post')
                ? [
                    'required',
                    'confirmed',
                    Password::min(8)           // минимум 8 символов
                        ->letters()            // хотя бы одна буква
                        ->mixedCase()          // хотя бы одна заглавная и одна строчная
                        ->numbers()            // хотя бы одна цифра
                        ->symbols()            // хотя бы один спецсимвол
                        ->uncompromised(),     // проверка на утечки через Have I Been Pwned
                ]
                : [
                    'nullable',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),
                ],
            // 🔑 Роль (ENUM)
            'role' => ['required', Rule::in(User::ROLES)],
            'specialty_id' => ['nullable', 'exists:specialties,id'],
            'is_department_head' => ['nullable', 'boolean'],
            'salary_override' => [
                'nullable',
                'integer',
                'min:0',
                function ($attr, $value, $fail) {
                    if ($this->boolean('is_department_head') && ! $value) {
                        $fail('Для начальника отдела необходимо указать индивидуальный оклад.');
                    }
                },
            ],
            'individual_bonus_percent' => 'nullable|integer|min:0|max:100', // <-- добавляем
        ];
    }
}
