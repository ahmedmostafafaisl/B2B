<?php

namespace App\Http\Controllers\Api\WhatsApp;

use Mpdf\Tag\Pre;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\DY365\DyService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\PreAppointmentMessage;
use App\Notifications\TechNotification;
use Illuminate\Support\Facades\Validator;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\WhatsApp\WhatsAppConfirmationService;
use App\Http\Requests\WhatsApp\SendWhatsAppMessageRequest;

class WhatsAppController extends Controller
{

    protected $dyService;

    public function __construct(DyService $dyService)
    {
        $this->dyService = $dyService;
    }

    public function notify(SendWhatsAppMessageRequest $request, WhatsAppService $whatsapp)
    {
        // log request
        Log::info('WhatsApp notify request received');
        $data = $request->validated();
        // log the request data
        Log::info('WhatsApp notify request data: ', $data);
        if ($data['notify_type'] === 'create') {
            $data['template'] = 'new_appointment_2';
            $parameters = [
                ["type" => "text", "text" => $data['name']],
                ["type" => "text", "text" => $data['type']],
                ["type" => "text", "text" => $data['date']],
                ["type" => "text", "text" => $data['items']],
            ];
        } elseif ($data['notify_type'] === 'update') {
            $data['template'] = 'appointment_modified';
            $parameters = [
                ["type" => "text", "text" => $data['name']],
                ["type" => "text", "text" => $data['type']],
                ["type" => "text", "text" => $data['date']],
            ];
        } elseif ($data['notify_type'] === 'confirmation') {
            $data['template'] = 'pre_appointment_action_v3';
            $parameters = [
                ["type" => "text", "text" => $data['name']],
                ["type" => "text", "text" => $data['type']],
                ["type" => "text", "text" => $data['date']],
                ["type" => "text", "text" => $data['items']],
            ];
        } else {
            return response()->json(['error' => 'Invalid notify type'], 400);
        }
        $today = now()->format('d/m/Y');
        if (isset($data['date']) && $data['date'] === $today) {    // start notification
            // ✅ Start notification — safely (even if it fails, continue)
            Log::channel('notifications')->info('new Appointment notify request received', [
                'request' => request()->all()
            ]);
            try {
                $user = User::where('tech_id', $data['worker_id'])->first();
                if ($user && $user->fcm_token) {
                    $user->notify(new TechNotification(
                        "You have a new appointment",
                        "New Appointment Received",
                        $data['notify_type'],
                        ['bookId' => $request->bookId ?? null, 'sales_order' => $request->bookId ?? null]
                    ));
                } else {
                    Log::warning('TechNotification: User not found or missing FCM token', [
                        'worker_id' => $data['worker_id'] ?? null,
                        'bookId' => $data['bookId'] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                // Log error but continue WhatsApp message
                Log::error('TechNotification failed: ' . $e->getMessage(), [
                    'worker_id' => $data['worker_id'] ?? null,
                    'bookId' => $data['bookId'] ?? null,
                ]);
            }
        }

        // ✅ Continue WhatsApp message sending even if notification failed
        // end notification
        try {
            $response = $whatsapp->sendTemplateMessage(
                $data['phone'],
                $data['template'],
                $parameters,
                "ar"
            );

            // If WhatsApp API responded with error JSON
            if (isset($response['error'])) {
                return response()->json([
                    'error' => $response['error']
                ], 400);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            // Catch exceptions like network errors
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => 'Exception',
                    'code' => $e->getCode(),
                ]
            ], 500);
        }
    }

    public function sendPreAppointmentMessage(Request $request, WhatsAppConfirmationService $whatsapp)
    {
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|string|min:8|max:20',
            'sales_order'  => 'required|string',
            'book_id'      => 'required|string',
            'appointment_id' => 'required',
            'worker_id'    => 'required',
            'customer_id'  => 'required',
            'name'         => 'required|string',
            'type'         => 'nullable|string',
            'date'         => 'required|date',
            'items'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['type'] = $data['type'] ?? 'Service';
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');
        $data['items'] = $data['items'] ?? '-';

        // ✅ 1. Save record before sending
        $message = PreAppointmentMessage::create($data);

        $bodyParameters = [
            ["type" => "text", "text" => $data['name']],
            ["type" => "text", "text" => $data['type']],
            ["type" => "text", "text" => $data['date']],
            ["type" => "text", "text" => $data['items']],
        ];

        $buttonParameters = [
            ["payload" => "{$data['appointment_id']}Yes"],
            ["payload" => "{$data['appointment_id']}Reschedule"],
            ["payload" => "{$data['appointment_id']}Not interested"],
        ];

        try {
            $response = $whatsapp->sendTemplateMessage(
                $data['phone'],
                'pre_appointment_action_v3',
                $bodyParameters,
                $buttonParameters,
                'ar'
            );
            Log::info(
                'WhatsApp sendPreAppointmentMessage response',
                $response->json()
            );


            // ✅ Check if message was accepted
            $isSent = isset($response['messages'][0]['message_status']) &&
                $response['messages'][0]['message_status'] === 'accepted';

            // ✅ Update record after sending
            $message->update([
                'is_sent'  => $isSent,
                'response' => json_encode($response),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Message sent successfully',
                'data'    => [
                    'phone' => $data['phone'],
                    'sales_order' => $data['sales_order'],
                    'is_sent' => $isSent,
                    'response' => $response,
                ],
            ]);
        } catch (\Throwable $e) {
            // ❌ Update failed status
            $message->update([
                'is_sent' => false,
                'response' => $e->getMessage(),
            ]);

            Log::error('Failed to send WhatsApp message: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to send WhatsApp message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function templates(WhatsAppService $whatsapp)
    {
        try {
            $response = $whatsapp->getTemplates();

            // If WhatsApp API responded with error JSON
            if (isset($response['error'])) {
                return response()->json([
                    'error' => $response['error']
                ], 400);
            }

            return response()->json($response);
        } catch (\Exception $e) {
            // Catch exceptions like network errors
            return response()->json([
                'error' => [
                    'message' => $e->getMessage(),
                    'type' => 'Exception',
                    'code' => $e->getCode(),
                ]
            ], 500);
        }
    }


    protected $verify_token = 'naqi_whatsapp_token';

    public function receive(Request $request)
    {
        Log::channel('whatsapp')->info('📩 WhatsApp webhook received', $request->all());

        // ✅ Step 1: GET Verification
        if ($request->isMethod('get')) {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === '00IZCYL5Ab1vafxxKfc6ihAMxTz4cVQH') {
                return response($challenge, 200);
            }

            return response('Verification token mismatch', 403);
        }

        // ✅ Step 2: POST Notification
        $entry = $request->input('entry.0.changes.0.value');

        if (!$entry || !isset($entry['messages'][0])) {
            return response()->json(['status' => 'ok']);
        }

        $message = $entry['messages'][0];

        Log::channel('whatsapp')->info('💬 Incoming WhatsApp message', [
            'from' => $message['from'] ?? null,
            'type' => $message['type'] ?? null,
            'text' => $message['button']['text'] ?? null,
        ]);

        if ($message['type'] !== 'button') {
            return response()->json(['status' => 'ok']);
        }

        $payload = $message['button']['payload'] ?? '';
        $text    = $message['button']['text'] ?? '';

        preg_match('/^(\d+)/', $payload, $matches);
        $appointmentId = $matches[1] ?? null;

        $textMap = [
            'تم' => 'confirm',
            'yes' => 'confirm',
            'Yes' => 'confirm',
            'بعدين' => 'reschedule',
            'reschedule' => 'reschedule',
            'Reschedule' => 'reschedule',
            'ماودي' => 'cancel',
            'not interested' => 'cancel',
            'Not interested' => 'cancel',
        ];

        $customerResponse = $textMap[$text] ?? null;

        if (!$appointmentId || !$customerResponse) {
            Log::channel('whatsapp')->warning('⚠️ Invalid payload or text', compact('payload', 'text'));
            return response()->json(['status' => 'ok']);
        }

        $appointment = PreAppointmentMessage::where('appointment_id', $appointmentId)
            ->latest()
            ->first();

        if (!$appointment) {
            Log::channel('whatsapp')->warning('⚠️ Appointment not found', compact('appointmentId'));
            return response()->json(['status' => 'ok']);
        }

        // تحديث رد العميل
        $appointment->update(['customer_response' => $customerResponse]);

        $requestBody = [
            '_contract' => [
                'bookId'       => $appointment->book_id,
                'salesOrderId' => $appointment->sales_order,
                'actionOwner'  => 1,
                'requestType'  => match ($customerResponse) {
                    'confirm'    => 2,
                    'cancel'     => 0,
                    'reschedule' => 1,
                },
            ],
        ];

        Log::channel('whatsapp')->info('📤 Sending request to Dy365', [
            'appointment_id' => $appointmentId,
            'request_body'   => $requestBody,
        ]);

        try {
            $response = $this->dyService->sendRequest3(
                'post',
                $this->dyService->customerChangeRequest,
                $requestBody
            );

            // ❌ Dy365 Error
            if (!$response || ($response['ok'] ?? false) === false) {

                Log::channel('whatsapp')->error('❌ Dy365 rejected request', [
                    'appointment_id' => $appointmentId,
                    'response'       => $response,
                ]);

                $appointment->update([
                    'flag'        => 0,
                    'dy_response' => $response,
                ]);
            } else {
                // ✅ Success
                Log::channel('whatsapp')->info('✅ Dy365 request success', [
                    'appointment_id' => $appointmentId,
                    'response'       => $response,
                ]);

                $appointment->update([
                    'flag'        => 1,
                    'dy_response' => $response,
                ]);
            }
        } catch (\Throwable $e) {

            Log::channel('whatsapp')->error('❌ Dy365 Exception', [
                'appointment_id' => $appointmentId,
                'error'          => $e->getMessage(),
            ]);

            $appointment->update([
                'flag'        => 0,
                'dy_response' => [
                    'exception' => $e->getMessage(),
                ],
            ]);
        }

        return response()->json(['status' => 'ok']);
    }




    public function verifyWebhook(Request $request)
    {
        $verifyToken = '00IZCYL5Ab1vafxxKfc6ihAMxTz4cVQH';

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            Log::info('✅ Webhook verified');
            return response($request->get('hub_challenge'), 200);
        }

        Log::error('❌ Invalid verify token');
        return response('Invalid verify token', 403);
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info('📩 WhatsApp POST payload', $payload);

        if (!isset($payload['entry'][0]['changes'][0]['value'])) {
            return response()->json(['status' => 'invalid'], 400);
        }

        $value = $payload['entry'][0]['changes'][0]['value'];

        // 🟢 When user clicks a button
        if (isset($value['messages'][0]['type']) && $value['messages'][0]['type'] === 'button') {
            $message = $value['messages'][0];
            $payloadText = $message['button']['payload'] ?? '';

            Log::info("🎯 Button clicked payload: " . $payloadText);

            // Extract appointment_id + action from payload
            preg_match('/(\d+)(Yes|Reschedule|Not interested)/i', $payloadText, $matches);

            if (count($matches) === 3) {
                $appointmentId = $matches[1];
                $actionType = strtolower(str_replace(' ', '_', $matches[2]));

                Log::info("🧩 Appointment {$appointmentId} - Action: {$actionType}");

                $appointment = PreAppointmentMessage::where('appointment_id', $appointmentId)
                    ->orderByDesc('created_at')
                    ->first();

                if ($appointment) {
                    switch ($actionType) {
                        case 'yes':
                            $appointment->update(['customer_response' => 'confirm']);
                            app(DyService::class)->submitCustomerChangeRequest(
                                [
                                    "_contract" => [
                                        'bookId'        => $appointment->book_id,
                                        'actionOwner'  => 2,
                                        'salesOrderId'  => $appointment->sales_order,
                                        'requestType'   => 2, // Confirmed
                                    ]
                                ]

                            );
                            break;

                        case 'reschedule':
                            $appointment->update(['customer_response' => 'reschedule']);
                            app(DyService::class)->submitCustomerChangeRequest(
                                [
                                    "_contract" => [
                                        'bookId'        => $appointment->book_id,
                                        'salesOrderId'  => $appointment->sales_order,
                                        'actionOwner'  => 2,
                                        'requestType'   => 1, // Reschedule

                                    ]
                                ]

                            );
                            break;

                        case 'not_interested':
                            $appointment->update(['customer_response' => 'cancel']);
                            app(DyService::class)->submitCustomerChangeRequest(
                                [
                                    "_contract" => [
                                        'bookId'        => $appointment->book_id,
                                        'actionOwner'  => 2,
                                        'salesOrderId'  => $appointment->sales_order,
                                        'requestType'   => 0, // Cancel
                                    ]
                                ]

                            );
                            break;
                    }
                } else {
                    Log::warning("⚠️ No PreAppointmentMessage found for appointment {$appointmentId}");
                }
            }
        }

        // 🟠 Status updates (sent/delivered/read)
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                Log::info('📬 Message status update', $status);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    //https://graph.facebook.com/v17.0/109567455104882/messages

    public function sendMessage($to, $message = null)
    {

        $url = "https://graph.facebook.com/v20.0/109567455104882/messages";
        $token = env('WHATSAPP_ACCESS_TOKEN') ?? "";

        $response = Http::withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $message ?? 'Hello from Laravel WhatsApp Bot!',
            ],
        ]);

        Log::info('📤 Sent message response', $response->json());

        return $response->json();
    }
}
