<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetWebApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user->can('resetWebApiKey', $user);
    }

    public function rules(): array
    {
        return [];
    }
}
