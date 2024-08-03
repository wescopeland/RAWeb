<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('updateProfileSettings', $user);
    }

    public function rules(): array
    {
        return [
            'motto' => 'nullable|string|max:50',
            'userWallActive' => 'nullable|boolean',
        ];
    }
}
