<?php

namespace App\Repositories\Faq;

use App\Models\Faq;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqRepository implements FaqRepositoryInterface
{
    public function index(Model $faqable, Request $request): array
    {
        $query = $faqable->faqs()->orderBy('sort_order')->orderByDesc('id');
        return $this->paginate($query, $request);
    }

    public function store(Model $faqable, array $data)
    {
        return $faqable->faqs()->create($data);
    }
public function bulkStore(Model $faqable, array $faqs): array
{
    $type = get_class($faqable);
    $id   = $faqable->id;

    $now = now();

    $rows = collect($faqs)->map(function ($f) use ($type, $id, $now) {
        return [
            // ✅ required morph columns
            'faqable_type' => $type,
            'faqable_id'   => $id,

            'question' => $f['question'],
            'answer' => $f['answer'],
            'sort_order' => $f['sort_order'] ?? 0,
            'is_active' => $f['is_active'] ?? true,

            'created_at' => $now,
            'updated_at' => $now,
        ];
    })->values()->toArray();

    // ✅ insert directly
    \App\Models\Faq::query()->insert($rows);

    // return latest inserted items (same batch size)
    return \App\Models\Faq::query()
        ->where('faqable_type', $type)
        ->where('faqable_id', $id)
        ->orderByDesc('id')
        ->take(count($rows))
        ->get()
        ->reverse()
        ->values()
        ->all();
}


// ...

public function bulkUpdate(Model $faqable, array $faqs): array
{
    return DB::transaction(function () use ($faqable, $faqs) {

        $ids = collect($faqs)->pluck('id')->unique()->values()->all();

        // ✅ security: ensure these FAQs belong to this faqable
        $allowedIds = Faq::query()
            ->where('faqable_type', get_class($faqable))
            ->where('faqable_id', $faqable->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $allowedIds = array_flip($allowedIds);

        foreach ($faqs as $row) {
            $id = (int) $row['id'];
            if (!isset($allowedIds[$id])) continue;

            $data = array_filter([
                'question' => $row['question'] ?? null,
                'answer' => $row['answer'] ?? null,
                'sort_order' => $row['sort_order'] ?? null,
                'is_active' => $row['is_active'] ?? null,
            ], fn ($v) => $v !== null);

            if (!empty($data)) {
                Faq::query()->where('id', $id)->update(array_merge($data, [
                    'updated_at' => now(),
                ]));
            }
        }

        return $faqable->faqs()->orderBy('sort_order')->orderBy('id')->get()->all();
    });
}

public function bulkReplace(Model $faqable, array $faqs): array
{
    return \Illuminate\Support\Facades\DB::transaction(function () use ($faqable, $faqs) {

        $type = get_class($faqable);
        $id   = $faqable->id;
        $now  = now();

        $faqable->faqs()->delete();

        $rows = collect($faqs)->map(function ($f, $i) use ($type, $id, $now) {
            return [
                'faqable_type' => $type,
                'faqable_id'   => $id,
                'question' => $f['question'],
                'answer' => $f['answer'],
                'sort_order' => $f['sort_order'] ?? $i,
                'is_active' => $f['is_active'] ?? true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->values()->toArray();

        if (!empty($rows)) {
            \App\Models\Faq::query()->insert($rows);
        }

        return $faqable->faqs()->orderBy('sort_order')->orderBy('id')->get()->all();
    });
}

   public function find(int $id)
    {
        return Faq::findOrFail($id);
    }

    public function update(int $id, array $data)
    {
        $faq = Faq::findOrFail($id);
        $faq->update($data);
        return $faq;
    }

    public function delete(int $id): void
    {
        Faq::findOrFail($id)->delete();
    }

    private function paginate($query, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ];
    }
}
