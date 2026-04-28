<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostVoidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'reason' => ['required', 'string'],
        ];
    }
}
