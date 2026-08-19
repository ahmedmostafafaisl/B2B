<?php

namespace App\Services\Payment;

use App\Models\Appointment;
use Illuminate\Support\Str;
use App\Models\DyPaymentLink;
use App\Models\ClickPayPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ClickPayService
{
    private $environment = 'test'; // 'production' or 'test'
    protected string $baseUrl;
    protected string $serverKey;
    protected int $profileId;

    public function __construct()
    {
        if ($this->environment == 'production') {
            // live keys
            $this->baseUrl   = "https://secure.clickpay.com.sa" ?? rtrim(config('services.clickpay.base_url'), '/') ?? 'https://secure.clickpay.com.sa';
            $this->serverKey = "SBJNL9NZ2R-J6NZLTJKDZ-GZGJTKTLMG" ?? config('services.clickpay.server_key') ?? 'SBJNL9NZ2R-J6NZLTJKDZ-GZGJTKTLMG';
            $this->profileId = "43354" ?? config('services.clickpay.profile_id') ?? 43354;
        } else {
            // test keys
            $this->baseUrl   = "https://secure.clickpay.com.sa" ?? rtrim(config('services.clickpay.base_url'), '/') ?? 'https://secure.clickpay.com.sa';
            $this->serverKey = "SHJNL9NZNH-J6NJN2TWZN-TMNJRBDNW9" ?? config('services.clickpay.server_key') ?? 'SHJNL9NZNH-J6NJN2TWZN-TMNJRBDNW9';
            $this->profileId = "43315" ?? config('services.clickpay.profile_id') ?? 43315;
        }
    }

    /**
     * Create ClickPay payment
     */

    public function createInvoice(Appointment $appointment, float $amount,  $phone, $isSingle): array
    {
        $user = $appointment->customer ?? auth()->user();
        $address = $appointment->appAddress;

        $payload = [
            'profile_id'       => $this->profileId,
            'tran_type'        => 'sale',
            'tran_class'       => 'ecom',
            'cart_id'          => 'ORDER-' . Str::uuid(),
            'cart_currency'    => 'SAR',
            'cart_amount'      => round($amount, 2), // total amount for all sales lines
            'cart_description' => "Appointment #{$appointment->id}",
            'paypage_lang'     => 'en',

            'customer_details' => [
                'name'     => $user->username ?? 'first last',
                'email'    => $user->email ?? 'email@domain.com',
                'phone'    => $phone ?? '0522222222',
                'street1'  => $address->street ?? 'address street',
                'city'     => $address->city ?? 'Riyadh',
                'state'    => 'Ar Riyadh',
                'country'  => 'SA',
                'zip'      => $address->zip ?? '12345',
                'ip'       => request()->ip() ?? '1.1.1.1',
            ],

            'shipping_details' => [
                'name'     => $user->username ?? 'Receiver Name',
                'email'    => $user->email ?? 'email1@domain.com',
                'phone'    => $user->phone ?? '971555555555',
                'street1'  => $address->street ?? 'Street 123',
                'city'     => $address->city ?? 'Riyadh',
                'state'    => 'Ar Riyadh',
                'country'  => 'SA',
                'zip'      => $address->zip ?? '54321',
                'ip'       => request()->ip() ?? '2.2.2.2',
            ],

            // Pass sales_ids as array in the URL
            'callback' => route('clickpay.callback', [
                'reference_id' => $appointment->id,
                'payment_type' => "clickpay",
                'type' => 'appointment',
                'is_single' => $isSingle,
            ]),
            'return' => route('clickpay.return', [
                'reference_id' => $appointment->id,
                'payment_type' => "clickpay",
                'type' => 'appointment',
                'amount' => $amount
            ]),
        ];

        try {
            $response = Http::withHeaders([
                'authorization' => $this->serverKey,
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/payment/request", $payload);

            $json = $response->json();

            if (!empty($json['redirect_url'])) {
                // Store  ClickPay payment info
                ClickPayPayment::create([
                    'user_id' => $user->id,
                    'reference_id' => $json['tran_ref'],
                    'appointment_id' => $appointment->id,
                    'session_url' => $json['redirect_url'],
                    'amount' => $amount,
                    'status' => 'created',
                ]);
                return [
                    'success' => true,
                    'redirect_url' => $json['redirect_url'],
                    'reference_id' => $json['tran_ref'],
                ];
            }

            return [
                'success'  => false,
                'message'  => $json['response']['message'] ?? 'Payment initiation failed',
                'response' => $json['response'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('ClickPay Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    public function createInvoiceNew($payment, $price, $phone, $sales_order_id)
    {
        $reference_id = $payment['reference_id'];
        $payload = [
            'profile_id'       => $this->profileId,
            'tran_type'        => 'sale',
            'tran_class'       => 'ecom',
            'cart_id'          => 'ORDER-' . Str::uuid(),
            'cart_currency'    => 'SAR',
            'cart_amount'      => round($price, 2), // total amount for all sales lines
            'cart_description' => "Appointment #{$reference_id}",
            'paypage_lang'     => 'en',

            'customer_details' => [
                'name'     =>   'first last',
                'email'    => 'email@domain.com',
                'phone'    => $phone ?? '0522222222',
                'street1'  => 'address street',
                'city'     => 'Riyadh',
                'state'    => 'Ar Riyadh',
                'country'  => 'SA',
                'zip'      => '12345',
                'ip'       => request()->ip() ?? '1.1.1.1',
            ],

            'shipping_details' => [
                'name'     => 'Receiver Name',
                'email'    => 'email1@domain.com',
                'phone'    => '971555555555',
                'street1'  => 'Street 123',
                'city'     => 'Riyadh',
                'state'    => 'Ar Riyadh',
                'country'  => 'SA',
                'zip'      => '54321',
                'ip'       => request()->ip() ?? '2.2.2.2',
            ],

            // Pass sales_ids as array in the URL
            'callback' => route('new.clickpay.callback', [
                'reference_id' => $reference_id,
                'sales_order_id' => $sales_order_id,
            ]),
            'return' => route('new.clickpay.return', [
                'reference_id' => $reference_id,
                'sales_order_id' => $sales_order_id,
            ]),
        ];

        try {
            $response = Http::withHeaders([
                'authorization' => $this->serverKey,
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/payment/request", $payload);

            $json = $response->json();

            // dd($json);
            if (!empty($json['redirect_url'])) {
                $payment->reference_id = $json['tran_ref'];
                $payment->save();
                return [
                    'success' => true,
                    'redirect_url' => $json['redirect_url'],
                    'reference_id' => $json['tran_ref'],
                ];
            }

            return [
                'success'  => false,
                'message'  => $json['response']['message'] ?? 'Payment initiation failed',
                'response' => $json['response'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('ClickPay Error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    public function dyCreateInvoice($data, $payment, float $amount, array $overrides = [])
    {
        $payload = [
            'profile_id'       => $this->profileId,
            'tran_type'        => 'sale',
            'tran_class'       => 'ecom',
            'cart_id'          => $overrides['cart_id'] ?? 'ORDER-' . Str::uuid(),
            'cart_currency'    => 'SAR',
            'cart_amount'      => round($amount, 2),
            'cart_description' => $overrides['description'] ?? "Appointment #{$data['phone']}",
            'paypage_lang'     => $overrides['lang'] ?? 'en',

            'customer_details' => [
                'name'     => $data['name'] ?? 'first last',
                'email'    => $data['email'] ?? 'email@domain.com',
                'phone'    => $data['phone'] ?? '0522222222',
                'street1'  => $data['street1'] ?? 'address street',
                'city'     => $data['city'] ?? 'Riyadh',
                'state'    => $data['state'] ?? 'Ar Riyadh',
                'country'  => 'SA',
                'zip'      => $data['zip'] ?? '12345',
                'ip'       => request()->ip() ?? '1.1.1.1',
            ],

            'shipping_details' => [
                'name'     => $data['name'] ?? 'Receiver Name',
                'email'    => $data['email'] ?? 'email1@domain.com',
                'phone'    => $data['phone'] ?? '971555555555',
                'street1'  => $data['street1'] ?? 'Street 123',
                'city'     => $data['city'] ?? 'Riyadh',
                'state'    => $data['state'] ?? 'Ar Riyadh',
                'country'  => 'SA',
                'zip'      => $data['zip'] ?? '54321',
                'ip'       => request()->ip() ?? '2.2.2.2',
            ],

            'callback' => route('clickpay.callback', [
                'reference_id' => $payment['reference_id'],
                'payment_type' => $payment['payment_type'],
                'type' =>  'dy',
            ]),
            'return' => route('clickpay.return', [
                'reference_id' => $payment['reference_id'],
                'payment_type' => $payment['payment_type'],
                'type' =>  'dy',
            ]),
        ];

        // Optionally include card_details for direct charge
        if (isset($overrides['card_details'])) {
            $payload['card_details'] = $overrides['card_details'];
        }

        try {
            $response = Http::withHeaders([
                'authorization' => $this->serverKey,
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/payment/request", $payload);

            $json = $response->json();

            if (
                ($json['redirect_url'])
            ) {

                // Save clickpay session info
                DyPaymentLink::create([
                    'payment_method' => 'clickpay',
                    'payment_reference_id' => $json['tran_ref'],
                    'dy_reference_id' => $payment['reference_id'],
                    'checkout_url' => $json['redirect_url'],
                    'status' => 'created',
                    'amount' => $amount,
                    'phone' => $data['phone'] ?? '0522222222',
                ]);
                return [
                    'success'      => true,
                    'redirect_url' => $json['redirect_url'],
                    'transaction'  => $json,
                    'reference_id' => $json['tran_ref'],
                ];
            }

            return [
                'success'  => false,
                'message'  => $json['response']['message'] ?? 'Payment initiation failed',
                'response' => $json['response'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('ClickPay Error', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ];
        }
    }


    public function handleReturn($tran_ref)
    {
        $payload = [
            'tran_ref'   => $tran_ref,
            'profile_id' => $this->profileId,
        ];

        $response = Http::withHeaders([
            'authorization' => $this->serverKey,
            'content-type'  => 'application/json',
        ])->post("{$this->baseUrl}/payment/query", $payload);

        // return ($response->json());
        if (! $response->successful()) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to query payment',
                'data'    => $response->json()
            ], 500);
        }

        return $data = $response->json();

        // ✅ Check authorised
        if (($data['payment_result']['response_message'] ?? null) !== 'Authorised') {
            return response()->json([
                'status'  => false,
                'message' => 'Payment not authorised',
                'data'    => $data
            ], 400);
        }

        // ✅ Update DB payment
        $payment = DyPaymentLink::where('payment_reference_id', $tran_ref)->first();
        if (! $payment) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $payment->update(['status' => 'success']);

        // ✅ Capture payload (use data from query response)
        $capturePayload = [
            "profile_id"       =>   $this->profileId, // use returned profileId
            "tran_type"        => "Sale",
            "tran_class"       => "ecom",
            "cart_id"          => $data['cart_id'] . $tran_ref,
            "cart_currency"    => $data['cart_currency'],
            "cart_amount"      => $data['cart_amount'],
            "cart_description" => $data['cart_description'],
            "tran_ref"         => $tran_ref, // sometimes needs to be $data['tran_ref'] or previous_tran_ref
        ];

        $captureResponse = Http::withHeaders([
            'authorization' => $this->serverKey,
            'content-type'  => 'application/json',
        ])->post("{$this->baseUrl}/payment/request", $capturePayload);

        return response()->json($captureResponse->json());
    }

    /**
     * Process refund
     */
    public function refund(string $tranRef, float $amount, string $reason = 'Customer refund'): array
    {
        $payload = [
            'tran_ref'      => $tranRef,
            'refund_amount' => $amount,
            'refund_reason' => $reason,
        ];

        $response = Http::withHeaders([
            'authorization' => $this->serverKey,
            'content-type'  => 'application/json',
        ])->post("{$this->baseUrl}/v2/refund", $payload);

        return $response->json();
    }

    /**
     * Parse webhook or return data
     */
    public function handleCallback(array $data): array
    {
        return $data; // Optional: verify or store transaction
    }
}
