<?php

namespace App\Repositories\Subject;

use App\Models\Subject;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use Illuminate\Http\Request;

class SubjectRepository implements SubjectRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = Subject::query()->orderByDesc('id');
        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Subject
    {
        return Subject::query()->findOrFail($id);
    }

    public function store(array $data): Subject
    {
        return Subject::create($data);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject->refresh();
    }

    public function delete(Subject $subject): void
    {
        $subject->delete();
    }

    // ✅ your pagination format
    private function paginate($query, Request $request): array
    {
        $perPage = (int) $request->input('per_page', 10);
        $currentPage = (int) $request->input('currentPage', 1);

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage);

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'total_pages'  => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total_items'  => $paginator->total(),
            ],
        ];
    }
}
