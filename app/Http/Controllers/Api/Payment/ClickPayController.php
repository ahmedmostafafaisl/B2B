<?php

namespace App\Http\Controllers\Api\Payment;

use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\DyPaymentLink;
use App\Models\AppointmentLine;
use App\Models\ClickPayPayment;
use function PHPSTORM_META\type;
use App\Models\DirectAppointment;

use App\Services\DY365\DyService;
use App\Models\AppointmentPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\CompleteSuccessPaymentsJob;
use App\Models\DirectAppointmentPayment;
use App\Services\Payment\ClickPayService;
use App\Services\CheckCompleteStatus\CheckCompleteService;

class ClickPayController extends Controller
{
    protected ClickPayService $clickPay;
    public $dy_service;
    protected $checkCompleteService;

    public function __construct(ClickPayService $clickPay, DyService $dy_service, CheckCompleteService $checkCompleteService)
    {
        $this->clickPay = $clickPay;
        $this->dy_service = $dy_service;
        $this->checkCompleteService = $checkCompleteService;
    }

    /**
     * Initiate a ClickPay payment
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount'         => 'required|numeric|min:0.01',
        ]);

        $appointment = Appointment::with('customer', 'address')->findOrFail($validated['appointment_id']);
        $user = $appointment->customer;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment does not have a valid customer.'
            ], 422);
        }

        $customer = [
            'name'    => $user->username ?? 'Customer',
            'email'   => $user->email ?? 'no-reply@example.com',
            'phone'   => $user->phone ?? '0500000000',
            'city'    => optional($appointment->address)->city ?? 'Riyadh',
            'country' => 'SA',
        ];

        $description = "Payment for Appointment #{$appointment->id}";

        $payload = [
            'amount'      => $validated['amount'],
            'description' => $description,
            'customer'    => $customer,
        ];

        $response = $this->clickPay->createInvoice($appointment, $validated['amount'],  $user->phone, 1);

        if ($response['success'] && isset($response['redirect_url'])) {
            return response()->json([
                'success'     => true,
                'redirect_url' => $response['redirect_url'],
                'transaction' => $response['transaction'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Payment initiation failed.',
            'details' => $response['response'] ?? [],
        ], 422);
    }


    /**
     * Refund a transaction
     */
    public function refund(Request $request)
    {
        $request->validate([
            'tran_ref' => 'required|string',
            'amount'   => 'required|numeric|min:0.01',
            'reason'   => 'nullable|string|max:255',
        ]);

        $tranRef = $request->tran_ref;
        $amount = $request->amount;
        $reason = $request->reason ?? 'Customer refund';

        $response = $this->clickPay->refund($tranRef, $amount, $reason);

        if (isset($response['response_code']) && $response['response_code'] === '000') {
            return response()->json([
                'success' => true,
                'message' => 'Refund successful.',
                'response' => $response
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Refund failed.',
            'response' => $response
        ], 422);
    }

    /**
     * ClickPay return URL handler
     */
    public function handleReturn(Request $request)
    {

        $appointment_id = $request->input('reference_id');
        $paymentMethod  = $request->input('payment_type');
        $type           = $request->input('type');

        if ($type !== 'appointment') {
            return response()->json(['status' => 'error', 'message' => 'Unsupported type.'], 400);
        }

        $appointment = Appointment::find($appointment_id);
        if (!$appointment) {
            return response()->json(['status' => 'error', 'message' => 'Appointment not found.'], 404);
        }

        $results = [];


        $payment = ClickPayPayment::where('appointment_id', $appointment_id)
            ->latest()
            ->first();

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Payment not found.'], 404);
        }



        $clickAppPayment = AppointmentPayment::where('appointment_id', $appointment_id)
            ->where('payment_type', 'clickpay')
            ->latest()
            ->first();

        $clickAppPayment->status = 'Success';
        $clickAppPayment->payment_reference_id = $payment->reference_id;
        $clickAppPayment->save();


        $totalAmount     = $payment->amount ?? $clickAppPayment->total_price;
        $priceWithoutTax = round($totalAmount / 1.15, 2);
        $taxAmount       = round($totalAmount - $priceWithoutTax, 2);

        $data = $this->clickPay->handleReturn($payment->reference_id);

        if (
            isset($data['payment_result']['response_message']) &&
            $data['payment_result']['response_message'] === 'Authorised'
        ) {
            $payment->update(['status' => 'success']);
            if ($clickAppPayment) {
                $clickAppPayment->update(['status' => 'Success']);
            }

            if ($appointment->collect >= $payment->amount) {
                $appointment->collect -= $payment->amount;
                $appointment->save();
            }
            if ($appointment->paid < $appointment->total_price) {
                $appointment->paid += $payment->amount;
            }
            $appointment->save();

            $cashPayment = AppointmentPayment::where('appointment_id', $appointment->id)
                ->whereIn('payment_type', ['cash', 'pos']) // cash OR pos
                ->latest()
                ->first();

            $body = [
                "_contract" => [
                    "worker" => $appointment->technician->tech_id ?? null,
                    "SalesOrderId" => $appointment->sales_order_id,
                    "BookId" => $appointment->book_id,
                    "Discount" => $appointment->discount_value ?? 0,
                    "salesLines" => [
                        [
                            "PaymentReference" => $payment->reference_id,
                            "TotalAmount" => $payment->amount,
                            "PaymentMethod" => "E-Commerce"
                        ],
                        // want to send cash payment if exists
                        $cashPayment ? [
                            "PaymentReference" => $cashPayment->payment_reference_id,
                            "TotalAmount" => $cashPayment->total_price,
                            "PaymentMethod"    => strtoupper($cashPayment->payment_type) // CASH or POS
                        ] : null
                    ]
                ]
            ];

            $appointment->update([
                'status' => 'processing'
            ]);
            // CompleteSuccessPaymentsJob::dispatch($appointment, $body);
            $cmd = "php " . escapeshellarg(base_path('artisan')) . " payments:complete "
                . escapeshellarg($appointment->id) . " "
                . escapeshellarg(json_encode($body))
                . " > /dev/null 2>&1 &";

            exec($cmd);




            try {
                $this->getAppointmentBySalesOrder($appointment->id);
            } catch (\Throwable $e) {
                // Log::error('getAppointmentBySalesOrder failed: ' . $e->getMessage());
            }

            $appointment->save();

            $results[] = [
                'status'          => 'success',
                'priceWithoutTax' => $priceWithoutTax,
                'taxAmount'       => $taxAmount
            ];
            $appointment->update([
                'status' => 'processing'
            ]);
            $appointment->save();
        } else {
            $results[] = [
                'status'          => 'failed',
                'priceWithoutTax' => $priceWithoutTax,
                'taxAmount'       => $taxAmount
            ];
        }

        // ✅ Compute final status and details for Blade
        $overallStatus = collect($results)->every(fn($r) => $r['status'] === 'success') ? 'success' : 'failed';
        $last          = collect($results)->last();
        $priceWithoutTax = $last['priceWithoutTax'] ?? 0;
        $taxAmount       = $last['taxAmount'] ?? 0;

        // Get the last payment object for Blade
        $lastSalesLineId = $last['sales_line_id'] ?? null;
        $lastPayment = $lastSalesLineId
            ? ClickPayPayment::where('appointment_id', $appointment->id)
            ->where('sales_line_id', $lastSalesLineId)
            ->latest()
            ->first()
            : null;

        return view('Payment.result', [
            'status'        => $overallStatus,
            'appointment'   => $appointment,
            'results'       => $results,
            'payment_type'  => 'clickpay',
            'payment'       => $payment,
            // 'payment'       => $lastPayment,
            'phone'         => $appointment->phone ?? ($lastPayment->reference_id ?? null),
            'priceWithoutTax' => $priceWithoutTax,
            'taxAmount'     => $taxAmount,
        ]);
    }

    public function newHandleReturn(Request $request)
    {
        $sales_order_id = $request->sales_order_id ?? null;
        $directAppointment = DirectAppointment::where('sales_order_id', $sales_order_id)->orderByDesc('id')->first();
        // $payment = DirectAppointmentPayment::where('sales_order_id', $request->sales_order_id)->where('payment_type', 'E-COMMERCE')->orderBy('id', 'desc')->first();
        $payment = DirectAppointmentPayment::where('reference_id', $request->reference_id)->where('payment_type', 'E-COMMERCE')->orderBy('id', 'desc')->first();

        // dd($payment);
        if (!$payment) {
            return view('Payment.result', [
                'status' => 'failed',
                'payment_type'    => 'clickpay',
                'message' => 'Payment not found',
                "price" => 0,
                'payment'         => $payment,
                'priceWithoutTax' => 0,
                'taxAmount'       => 0,
            ]);
        }

        // ✅ Compute final status and details for Blade
        $totalAmount     = $payment->price;
        $priceWithoutTax = round($totalAmount / 1.15, 2);
        $taxAmount       = round($totalAmount - $priceWithoutTax, 2);

        // 🔹 Handle ClickPay response
        $data = $this->clickPay->handleReturn($payment->payment_id);

        $data = json_decode(json_encode($data), true);
        // return $data['payment_result']['response_message'];
        if (
            isset($data['payment_result']['response_message']) &&
            ($data['payment_result']['response_message']) === 'Authorised'
        ) {
            $payment->status = 'paid';
            $directAppointment->collect = max(0,  $directAppointment->collect -= $payment->price);
            $directAppointment->save();
        } else {
            if ($payment->status != 'paid') {
                $payment->status = 'failed';
            }
        }
        $payment->save();
        // 🔹 Check if all payments for this sales order are paid
        $paidSum = $directAppointment->payments()
            ->where('status', 'paid')
            ->sum('price');

        $discount = $directAppointment->discount ?? 0;
        $required = $directAppointment->required_amount ?? 0;

        // compare within small tolerance to handle floating-point decimals
        $allPaid = abs(($paidSum + $discount) - $required) < 0.01;
        if ($allPaid) {
            // ✅ Retrieve the related DirectAppointment
            $appointment = DirectAppointment::where('sales_order_id', $payment->sales_order_id)->orderByDesc('id')->first();
            if ($appointment) {
                $appointment->status = 'paid';
                $appointment->save();

                $tech = $appointment->technician; // ensure you have a relation set up

                // 🔹 Get all paid payments for this Sales Order
                $paidPayments = DirectAppointmentPayment::where('sales_order_id', $appointment->sales_order_id)
                    ->whereIn('payment_type', ['cash', 'pos', 'clickpay', 'TRNS'])
                    ->where('status', 'paid')
                    ->get();

                // 🔹 Build salesLines dynamically
                $discount = $appointment->discount ?? 0;
                // $salesLines = [[
                //     "TotalAmount"   => (float) $appointment->required_amount - $discount,
                //     "PaymentMethod" => "E-Commerce",
                // ]];
                // new sales lines

                $payments = $appointment->payments()
                    ->where('status', 'paid')
                    ->get()
                    ->unique('reference_id');

                $totalPayments = $payments->sum('price');

                $salesLines = $payments->map(function ($payment) {
                    return [
                        'TotalAmount'   => floatval($payment->price ?? 0),
                        'PaymentReference' => $payment->reference_id ?? null,
                        'PaymentMethod' => match ($payment->payment_type) {
                            'tabby'     => 'TABI',
                            'clickpay'  => 'E-Commerce',
                            default     => $payment->payment_type,
                        },
                    ];
                })->values()->toArray();

                // ✅ Build request body
                $body = [
                    "_contract" => [
                        "worker"       => $appointment->tech_id ?? null,
                        "SalesOrderId" => $appointment->sales_order_id,
                        "BookId"       => $appointment->book_id,
                        "Discount"     => $appointment->discount ?? 0,
                        "salesLines"   => $salesLines,
                    ]
                ];
                // check payments before sending to complete
                $check = $this->checkCompleteService->checkPayments($appointment->book_id, $body);
                if ($check['ok'] === true) {
                    // 🔹 Send background command
                    $cmd = "php " . escapeshellarg(base_path('artisan')) . " payments:complete "
                        . escapeshellarg($appointment->id)
                        . " " . escapeshellarg(json_encode($body))
                        . " > /dev/null 2>&1 &";
                    exec($cmd);
                } else {
                    Log::warning('Payment completion blocked', [
                        'appointment_id' => $appointment->id,
                        'reason'         => $check['reason'],
                    ]);
                }
            }
        }
        // dd($data);
        return view('Payment.result', [
            'status'          => $payment->status,
            'payment_type'    => 'clickpay',
            'payment'         => $payment,
            'phone'           => $payment->phone ?? $payment->reference_id,
            'priceWithoutTax' => $priceWithoutTax,
            'taxAmount'       => $taxAmount,
        ]);
    }


    /**
     * ClickPay callback (webhook) handler
     */
    public function handleCallback(Request $request)
    {
        return "handleCallback";
        $data = $this->clickPay->handleCallback($request->all());

        // Save or log transaction as needed
        // Log::info('ClickPay Callback Received:', $data);

        return response()->json([
            'message' => 'Callback processed',
            'data' => $data
        ]);
    }
}
