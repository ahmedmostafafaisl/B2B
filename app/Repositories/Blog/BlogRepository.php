<?php

namespace App\Repositories\Blog;

use App\Models\Blog;
use App\Models\BlogSection;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogRepository implements BlogRepositoryInterface
{
    public function index(Request $request): array
    {
        $query = Blog::query()
            ->with('sections')
            ->orderBy('sort_order')
            ->orderByDesc('id');

        return $this->paginate($query, $request);
    }

    public function findOrFail(int $id): Blog
    {
        return Blog::query()
            ->with('sections')
            ->findOrFail($id);
    }

    public function store(array $data): Blog
    {
        return DB::transaction(function () use ($data) {
            $sections = $data['sections'] ?? [];
            unset($data['sections']);

            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

            if (! empty($data['_image_file']) && $data['_image_file'] instanceof UploadedFile) {
                $data['image'] = $data['_image_file']->store('blogs', 's3');
            }

            unset($data['_image_file']);

            $blog = Blog::create($data);

            foreach ($sections as $index => $section) {
                BlogSection::create([
                    'blog_id' => $blog->id,
                    'type' => $section['type'],
                    'content' => $section['content'],
                    'sort_order' => $section['sort_order'] ?? ($index + 1),
                ]);
            }

            return $this->findOrFail($blog->id);
        });
    }

    public function update(Blog $blog, array $data): Blog
    {
        return DB::transaction(function () use ($blog, $data) {
            $sections = $data['sections'] ?? null;
            unset($data['sections']);

            if (isset($data['title']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            if (! empty($data['_remove_image'])) {
                $this->deleteFileIfExists($blog->image);
                $data['image'] = null;
            }

            if (! empty($data['_image_file']) && $data['_image_file'] instanceof UploadedFile) {
                $this->deleteFileIfExists($blog->image);
                $data['image'] = $data['_image_file']->store('blogs', 's3');
            }

            unset($data['_image_file'], $data['_remove_image']);

            $blog->update($data);

            if (is_array($sections)) {
                $blog->sections()->delete();

                foreach ($sections as $index => $section) {
                    BlogSection::create([
                        'blog_id' => $blog->id,
                        'type' => $section['type'],
                        'content' => $section['content'],
                        'sort_order' => $section['sort_order'] ?? ($index + 1),
                    ]);
                }
            }

            return $this->findOrFail($blog->id);
        });
    }

    public function delete(Blog $blog): void
    {
        DB::transaction(function () use ($blog) {
            $this->deleteFileIfExists($blog->image);
            $blog->delete();
        });
    }

    private function deleteFileIfExists(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = Storage::disk('s3');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }
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
