<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\UserPreference;
use Illuminate\Foundation\Http\FormRequest;

class WebsitePrefsRequest extends FormRequest
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
            'websitePrefs' => 'required|integer',
        ];
    }
}
