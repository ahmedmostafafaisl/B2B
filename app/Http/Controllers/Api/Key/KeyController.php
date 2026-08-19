<?php

namespace App\Http\Controllers\Api\Key;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Key\KeyResource;
use App\Http\Requests\Key\KeyStoreRequest;
use App\Http\Requests\Key\KeyUpdateRequest;
use App\Repositories\Interfaces\KeyRepositoryInterface;

class KeyController extends Controller
{
    public function __construct(private KeyRepositoryInterface $keys)
    {
    }

    public function index(Request $request)
    {
        $result = $this->keys->index($request);

        return response()->json([
            'items' => KeyResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(KeyStoreRequest $request)
    {
        $validated = $request->validated();

        $validated['_icon_file'] = $request->file('icon');
        $validated['_remove_icon'] = $request->boolean('remove_icon');

        // remove raw icon fields from validated array (optional; repo ignores anyway)
        unset($validated['icon'], $validated['remove_icon']);

        $item = $this->keys->store($validated);

        return (new KeyResource($item))->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        $item = $this->keys->findOrFail($id);
        return new KeyResource($item);
    }

    public function update(KeyUpdateRequest $request, int $id)
    {
        $item = $this->keys->findOrFail($id);

        $validated = $request->validated();

        $validated['_icon_file'] = $request->file('icon');
        $validated['_remove_icon'] = $request->boolean('remove_icon');

        unset($validated['icon'], $validated['remove_icon']);

        $updated = $this->keys->update($item, $validated);

        return new KeyResource($updated);
    }

    public function destroy(int $id)
    {
        $item = $this->keys->findOrFail($id);
        $this->keys->delete($item);

        return response()->json(['message' => 'Key deleted successfully']);
    }
}
