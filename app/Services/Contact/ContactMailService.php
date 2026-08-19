<?php

namespace App\Services\Contact;

use App\Mail\ContactDataMail;
use App\Models\Contact;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactMailService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function sendContactData(
        Contact $contact,
        string  $toEmail,
        ?string $emailNote  = null,
        ?string $contactUrl = null,
        array   $cc         = [],
    ): bool {
        $contactData = [
            'name'        => $contact->name,
            'email'       => $contact->email,
            'phone'       => $contact->phone,
            'subject'     => $contact->subject?->name ?? null,
            'status'      => $contact->status,
            'message'     => $contact->message,
            'note'        => $contact->note,
            'email_note'  => $emailNote,
            'contact_url' => $contactUrl,
        ];

        $mail = Mail::to($toEmail);

        if (!empty($cc)) {
            $mail->cc($cc);
        }

        $mail->send(new ContactDataMail($contactData));

        $this->activityLogService->record(
            model: $contact,
            action: 'send_mail',
            note: "Email sent to [{$toEmail}] by [" . (Auth::user()?->username ?? Auth::id()) . "]",
            meta: [
                'sent_to_email' => $toEmail,
                'cc'            => $cc,
                'email_note'    => $emailNote,
                'contact_url'   => $contactUrl,
                'data_sent'     => $contactData,
            ],
        );

        return true;
    }
}
