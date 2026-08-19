<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

trait SubServiceModelFetcher
{
    protected function getBySubServiceId(int $subServiceId, string $modelClass)
    {
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("Invalid model class: {$modelClass}");
        }

        $relations = match (class_basename($modelClass)) {
            'SubServiceFeature' => ['types.items'],
            'SubServiceModel'   => ['sections.items'],
            'SubServiceApplication' => [],
            'SubservienceSpecification' => [],
            'SubservienceReview' => [],
            'SubservienceDoc' => [],
                // add your other 3 models here with their relations
            default             => [],
        };

        $model = new $modelClass;
        $table = $model->getTable();

        $query = $modelClass::query()
            ->where('sub_service_id', $subServiceId)
            ->with($relations);

         if (Schema::hasColumn($table, 'sort_order')) {
            $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->get();
    }
}
