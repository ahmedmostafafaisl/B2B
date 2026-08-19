<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogStoreRequest;
use App\Http\Requests\Blog\BlogUpdateRequest;
use App\Http\Resources\Blog\BlogResource;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(private BlogRepositoryInterface $blogs) {}

    public function index(Request $request)
    {
        $result = $this->blogs->index($request);

        return response()->json([
            'items' => BlogResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(BlogStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['_image_file'] = $request->file('image');

        $blog = $this->blogs->store($validated);

        return (new BlogResource($blog))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $blog = $this->blogs->findOrFail($id);

        return new BlogResource($blog);
    }

    public function update(BlogUpdateRequest $request, int $id)
    {
        $blog = $this->blogs->findOrFail($id);

        $validated = $request->validated();
        $validated['_image_file'] = $request->file('image');
        $validated['_remove_image'] = $request->boolean('remove_image');

        $updated = $this->blogs->update($blog, $validated);

        return new BlogResource($updated);
    }

    public function destroy(int $id)
    {
        $blog = $this->blogs->findOrFail($id);
        $this->blogs->delete($blog);

        return response()->json(['message' => 'Blog deleted successfully']);
    }
}
