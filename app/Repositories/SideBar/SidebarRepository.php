<?php

namespace App\Repositories\SideBar;

use App\Models\MenuModule;
use App\Repositories\Interfaces\SidebarRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SidebarRepository implements SidebarRepositoryInterface
{
    public function sidebar(Request $request): array
    {
        $modules = MenuModule::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['items' => function ($q) {
                $q->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->with(['children' => function ($qq) {
                        $qq->where('is_active', true)->orderBy('sort_order');
                    }]);
            }])
            ->get();

        return ['items' => $modules];
    }

    public function getAllActive(string $modelClass, array $relations = [], array $filters = [], array $columns = ['*']): Collection
    {
        /** @var Model $instance */
        $instance = new $modelClass;

        // Always include 'id' to avoid relation loading issues
        if ($columns !== ['*'] && ! in_array('id', $columns)) {
            $columns[] = 'id';
        }

        return $modelClass::query()
            ->select($columns)
            ->when(
                $this->hasColumn($instance, 'is_active'),
                fn ($q) => $q->where('is_active', true)
            )
            ->when(
                ! empty($relations),
                fn ($q) => $q->with($relations)
            )
            ->when(
                ! empty($filters),
                function ($q) use ($filters) {
                    foreach ($filters as $column => $value) {
                        is_array($value)
                            ? $q->whereIn($column, $value)
                            : $q->where($column, $value);
                    }
                }
            )
            ->when(
                $this->hasColumn($instance, 'sort_order') && in_array('sort_order', $columns) || $columns === ['*'],
                fn ($q) => $q->orderBy('sort_order', 'asc')
            )
            ->orderByDesc('id')
            ->get();
    }

    public function getManyActive(array $models): array
    {
        $result = [];

        foreach ($models as $entry) {
            $modelClass = $entry['model'];
            $relations = $entry['relations'] ?? [];
            $filters = $entry['filters'] ?? [];
            $columns = $entry['columns'] ?? ['*'];
            $key = $entry['key'] ?? class_basename($modelClass);

            $result[$key] = $this->getAllActive($modelClass, $relations, $filters, $columns);
        }

        return $result;
    }

    private function hasColumn(Model $model, string $column): bool
    {
        return in_array($column, $model->getFillable());
    }
}
