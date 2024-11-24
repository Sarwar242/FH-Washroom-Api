<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ToiletExtendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->toilet->occupied_by === Auth::id();
    }

    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:255'
        ];
    }
}
