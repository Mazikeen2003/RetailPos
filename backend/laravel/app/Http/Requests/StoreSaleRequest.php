<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'string'],
        ];
    }
}
