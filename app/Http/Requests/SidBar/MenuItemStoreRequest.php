<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'menu_module_id' => ['required', 'exists:menu_modules,id'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],

            'title' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:500'],

            // polymorphic target (optional)
            'linkable_type' => ['nullable', 'string', 'max:255', Rule::in([
                \App\Models\Service::class,
                \App\Models\SubService::class,
            ])],
            'linkable_id' => ['nullable', 'integer'],

            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // if linkable_type exists but linkable_id missing => keep as is; controller/repo will validate existence
    }
}
