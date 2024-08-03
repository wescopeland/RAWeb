<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserResettableGameAchievementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The caller is always the target user.
        return true;
    }

    public function rules(): array
    {
        return [
            'gameId' => 'required|integer',
        ];
    }
}
