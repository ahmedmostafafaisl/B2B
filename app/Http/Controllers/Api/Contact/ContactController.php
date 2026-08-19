<?php

namespace App\Http\Controllers\Api\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactStoreRequest;
use App\Http\Requests\Contact\ContactUpdateRequest;
use App\Http\Resources\Contact\ContactResource;
use App\Models\Contact;
use App\Repositories\Interfaces\ContactRepositoryInterface;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(private ContactRepositoryInterface $contacts)
    {
        $this->middleware('auth:sanctum')->only(['update']);
    }

    public function index(Request $request)
    {
        $result = $this->contacts->index($request);

        return response()->json([
            'items' => ContactResource::collection($result['items']),
            'pagination' => $result['pagination'],
        ]);
    }

    public function store(ContactStoreRequest $request)
    {

        $contact = $this->contacts->store($request->validated());

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id)
    {
        $contact = $this->contacts->findOrFail($id);
        return new ContactResource($contact);
    }


    public function update(ContactUpdateRequest $request, Contact $contact)
    {

        $updated = $this->contacts->update($contact, $request->validated());

        return new ContactResource($updated);
    }

    public function destroy(int $id)
    {
        $contact = $this->contacts->findOrFail($id);
        $this->contacts->delete($contact);

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}
