<?php

namespace App\Repositories\Key;

use App\Models\Key;
use App\Repositories\Interfaces\KeyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KeyRepository implements KeyRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = Key::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('key', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Key
    {
        return Key::query()->findOrFail($id);
    }

    public function store(array $data): Key
    {
        // icon upload
        if (! empty($data['_icon_file'])) {
            $data['icon'] = $data['_icon_file']->store('keys/icons', 's3');
        }
        unset($data['_icon_file'], $data['_remove_icon']);

        return Key::create($data);
    }

    public function update(Key $key, array $data): Key
    {
        // remove icon
        if (! empty($data['_remove_icon'])) {
            $this->deleteFileIfExists($key->icon);
            $data['icon'] = null;
        }

        // replace icon
        if (! empty($data['_icon_file'])) {
            $this->deleteFileIfExists($key->icon);
            $data['icon'] = $data['_icon_file']->store('keys/icons', 's3');
        }

        unset($data['_icon_file'], $data['_remove_icon']);

        $key->update($data);

        return $key->refresh();
    }

    public function delete(Key $key): void
    {
        $this->deleteFileIfExists($key->icon);
        $key->delete();
    }

    private function deleteFileIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
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
                'total_pages' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
            ],
        ];
    }
}
