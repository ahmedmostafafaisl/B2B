<?php

namespace App\Http\Controllers\Api\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\SendContactMailRequest;
use App\Models\Contact;
use App\Services\Contact\ContactMailService;
use Illuminate\Http\JsonResponse;

class ContactMailController extends Controller
{
    public function __construct(
        private readonly ContactMailService $mailService
    ) {}

    public function send(SendContactMailRequest $request): JsonResponse
    {
        $validated  = $request->validated();
        $contact    = Contact::with('subject')->findOrFail($validated['contact_id']);
        $toEmail    = $validated['to_email'];
        $emailNote  = $validated['email_note']  ?? null;
        $contactUrl = $validated['contact_url'] ?? null;
        $cc         = $validated['cc']          ?? [];

        try {
            $this->mailService->sendContactData($contact, $toEmail, $emailNote, $contactUrl, $cc);

            return response()->json([
                'status'  => true,
                'message' => "Contact data sent successfully to {$toEmail}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
