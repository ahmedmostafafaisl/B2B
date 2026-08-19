<?php

namespace App\Http\Requests\SidBar;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ActiveItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    private array $allowedRelations = [
        'Service' => ['images', 'primaryImage', 'serviceTypes', 'serviceTypes.subServices', 'faqs'],
        'Part' => ['subParts', 'subParts.children', 'faqs', 'banners'],
    ];

    private array $allowedColumns = [
        'Service' => ['id', 'title', 'slug', 'description', 'is_active', 'sort_order', 'primary_image', 'subtitle', 'created_at', 'updated_at'],
        'Part' => ['id', 'title', 'subtitle', 'slug', 'description', 'is_active', 'sort_order', 'primary_image', 'banner', 'created_at', 'updated_at'],
    ];

    public function rules(): array
    {
        return [
            'models' => ['required', 'array', 'min:1'],
            'models.*.model' => ['required', 'string', 'in:Service,Part'],
            'models.*.relations' => ['nullable', 'array'],
            'models.*.relations.*' => ['string'],
            'models.*.filters' => ['nullable', 'array'],
            'models.*.columns' => ['nullable', 'array'],
            'models.*.columns.*' => ['string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ((array) $this->input('models', []) as $index => $entry) {
                $modelKey = $entry['model'] ?? null;
                $relations = $entry['relations'] ?? [];
                $columns = $entry['columns'] ?? [];

                if (! $modelKey) {
                    continue;
                }

                // ── Validate relations ────────────────────────────────
                if (! empty($relations)) {
                    $allowedRelations = $this->allowedRelations[$modelKey] ?? [];
                    $invalidRelations = array_diff($relations, $allowedRelations);

                    if (! empty($invalidRelations)) {
                        $validator->errors()->add(
                            "models.{$index}.relations",
                            "Invalid relations for [{$modelKey}]: ".implode(', ', $invalidRelations).'.'.
                            ' Allowed: '.implode(', ', $allowedRelations)
                        );
                    }
                }

                // ── Validate columns ──────────────────────────────────
                if (! empty($columns)) {
                    $allowedColumns = $this->allowedColumns[$modelKey] ?? [];
                    $invalidColumns = array_diff($columns, $allowedColumns);

                    if (! empty($invalidColumns)) {
                        $validator->errors()->add(
                            "models.{$index}.columns",
                            "Invalid columns for [{$modelKey}]: ".implode(', ', $invalidColumns).'.'.
                            ' Allowed: '.implode(', ', $allowedColumns)
                        );
                    }
                }
            }
        });
    }
}
