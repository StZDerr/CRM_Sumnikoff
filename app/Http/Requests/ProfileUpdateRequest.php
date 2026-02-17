<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => ['nullable', 'string', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'birth_date' => ['nullable', 'date'],
            // Новые поля профиля
            'position' => ['nullable', 'string', 'max:255'],
            'work_type' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'], // max 2MB
            'remove_avatar' => ['nullable', 'boolean'],

            'socials' => ['nullable', 'array'],
            'socials.*.platform' => ['nullable', Rule::in(['vk', 'telegram', 'maks'])],
            'socials.*.url' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Log incoming file info before validation so we can see if browser sent the file.
     */
    protected function prepareForValidation(): void
    {
        try {
            $has = $this->hasFile('avatar');
            $file = $has ? $this->file('avatar') : null;

            Log::info('ProfileUpdateRequest: prepareForValidation', [
                'user_id' => $this->user()?->id,
                'has_avatar_file' => $has,
                'file_original_name' => $file?->getClientOriginalName(),
                'file_size' => $file?->getSize(),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ProfileUpdateRequest: failed to log file info', ['error' => $e->getMessage()]);
        }
    }
}

