<?php

namespace App\Http\Controllers\Api\Subject;

use App\Http\Controllers\Controller;
 use App\Http\Requests\SubjectUpdateRequest;
use App\Http\Resources\Subject\SubjectResource;
use App\Http\Requests\Subject\SubjectStoreRequest;
use App\Repositories\Interfaces\SubjectRepositoryInterface;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function __construct(private SubjectRepositoryInterface $subjects)
    {
    }

    public function index(Request $request)
    {
        $result = $this->subjects->index($request);

        return response()->json([
            'items' => SubjectResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(SubjectStoreRequest $request)
    {
        $subject = $this->subjects->store($request->validated());
        return (new SubjectResource($subject))->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        $subject = $this->subjects->findOrFail($id);
        return new SubjectResource($subject);
    }

    public function update(SubjectUpdateRequest $request, int $id)
    {
        $subject = $this->subjects->findOrFail($id);
        $updated = $this->subjects->update($subject, $request->validated());
        return new SubjectResource($updated);
    }

    public function destroy(int $id)
    {
        $subject = $this->subjects->findOrFail($id);
        $this->subjects->delete($subject);

        return response()->json(['message' => 'Subject deleted successfully']);
    }
}
