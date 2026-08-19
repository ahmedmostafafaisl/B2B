<?php

namespace App\Http\Controllers\Api\Payment;

use App\Models\Appointment;
use App\Models\TabbyPayment;
use Illuminate\Http\Request;
use App\Imports\TamaraImport;
use App\Models\TamaraPayment;
use App\Models\AppointmentLine;
use App\Helper\ApiResponseHelper;
use App\Models\DirectAppointment;
use App\Services\DY365\DyService;
use App\Models\AppointmentPayment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Payment\TabbyService;
use App\Services\Payment\TamaraService;
use App\Jobs\CompleteSuccessPaymentsJob;
use App\Models\DirectAppointmentPayment;
use App\Services\CheckCompleteStatus\CheckCompleteService;


class TamaraPaymentController extends Controller
{
    use ApiResponseHelper;
    public $dy_service;
    protected $checkCompleteService;
    public function __construct(DyService $dy_service, CheckCompleteService $checkCompleteService)
    {
        $this->dy_service = $dy_service;
        $this->checkCompleteService = $checkCompleteService;
    }

    // tamara

    public function success(Request $request)
    {
        $orderId = $request->query('orderId');


        // Get payment for this sales line
        $payment = TamaraPayment::where('reference_id', $orderId)
            ->latest()
            ->first();

        if (!$payment) {
            // \Log::warning("TamaraPayment not found  for cancelled order {$orderId}");
            return response()->json(['error' => 'No matching payment records found'], 404);
        }
        // Update appointment payment
        $appPayment = AppointmentPayment::where('appointment_id', $request->appointment_id)
            ->where('payment_type', 'tamara')
            ->latest()
            ->first();

        $appPayment->status = 'Success';
        $appPayment->payment_reference_id = $orderId;
        $appPayment->save();


        if ($appPayment) {
            $appointment = $appPayment->appointment;
        } else {
            $appointment = $payment->appointment;
        }

        $tech = $appointment->technician;

        // Send DY complete success payment for this sales line
        $cashPayment = AppointmentPayment::where('appointment_id', $appointment->id)
            ->whereIn('payment_type', ['cash', 'pos', 'TRNS']) // cash OR pos
            ->latest()
            ->first();

        $body = [
            "_contract" => [
                "worker" => $tech->tech_id,
                "SalesOrderId" => $appointment->sales_order_id,
                "BookId" => $appointment->book_id,
                "Discount" => $appointment->discount_value ?? 0,
                "salesLines" => [
                    [
                        "PaymentReference" => $payment->reference_id,
                        "TotalAmount" => $payment->amount,
                        "PaymentMethod" => "Tamara"
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
            // \Log::error('getAppointmentBySalesOrder failed: ' . $e->getMessage());
        }

        // Deduct from collect
        if ($appointment->collect >= $payment->amount) {
            $appointment->collect -= $payment->amount;
            $appointment->save();
        }
        if ($appointment->paid < $appointment->total_price) {
            $appointment->paid += $payment->amount;
        }
        $appointment->update([
            'status' => 'processing'
        ]);
        $appointment->save();

        // Call Tamara API for this order
        $statusResponse = app(TamaraService::class)->getOrderStatus($orderId);

        if (isset($statusResponse['status'])) {
            if ($statusResponse['status'] === 'approved') {
                $authResponse = app(TamaraService::class)->authorizeOrder($orderId);
                if (isset($authResponse['status']) && $authResponse['status'] === 'authorised') {
                    $payment->status = 'authorised';
                    $payment->save();
                    if ($appPayment) {
                        $appPayment->status = 'Success';
                        $appPayment->save();
                    }

                    $captureResponse = app(TamaraService::class)->captureOrder($orderId);
                    if (isset($captureResponse['status']) && $captureResponse['status'] === 'fully_captured') {
                        if ($captureResponse['data']['status'] === 'fully_captured') {
                            $payment->status = 'fully_captured';
                        } elseif ($captureResponse['data']['status'] === 'captured') {
                            $payment->status = 'captured';
                        }
                    }
                    $payment->save();
                    if ($appPayment) {
                        $appPayment->status = $payment->status;
                        $appPayment->save();
                    }
                }
            } elseif ($statusResponse['status'] === 'authorised') {
                $captureResponse = app(TamaraService::class)->captureOrder($orderId);
                if (isset($captureResponse['status']) && $captureResponse['status'] === 'captured') {
                    if ($captureResponse['status'] === 'captured') {
                        $payment->status = 'captured';
                        $payment->save();
                        if ($appPayment) {
                            $appPayment->status = 'captured';
                            $appPayment->save();
                        }
                    }
                }
            }

            // Mark success regardless of status flow
            if ($appPayment) {
                $appPayment->status = 'Success';
                $appPayment->save();
            }
        }


        // Show result for the last processed payment
        $lastPayment = $payment ?? null;
        if ($lastPayment) {
            $appointment = $lastPayment->appointment;
            $totalAmount = $lastPayment->amount;
            $priceWithoutTax = round($totalAmount / 1.15, 2);
            $taxAmount = round($totalAmount - $priceWithoutTax, 2);

            return view('Payment.result', [
                'status' => 'success',
                'payment_type' => 'tamara',
                'payment' => $lastPayment,
                'phone' => $appointment->phone ?? $lastPayment->reference_id,
                'priceWithoutTax' => $priceWithoutTax,
                'taxAmount' => $taxAmount,
            ]);
        }

        return response()->json(['message' => 'No payments processed'], 404);
    }

    public function failure(Request $request)
    {
        $orderId = $request->query('orderId');
        $lastPayment = null;
        // Find payment for this sales line
        $payment = TamaraPayment::where('reference_id', $orderId)
            ->latest()
            ->first();

        if (!$payment) {
            // \Log::warning("TamaraPayment not found  for failure order {$orderId}");
            return response()->json(['error' => 'No matching payment records found'], 404);
        }

        $lastPayment = null;

        // foreach ($salesIds as $salesLineId) {
        // Get payment for this sales line
        $payment = TamaraPayment::where('reference_id', $orderId)
            ->latest()
            ->first();

        if (!$payment) {
            // \Log::warning("TamaraPayment not found  for cancelled order {$orderId}");
            return response()->json(['error' => 'No matching payment records found'], 404);
        }

        // Mark payment as failed
        $payment->status = 'failed';
        $payment->save();

        // Update appointment payment record
        $tamaraAppPayment = AppointmentPayment::where('payment_reference_id', $orderId)
            ->where('payment_type', 'tamara')
            ->latest()
            ->first();

        if ($tamaraAppPayment) {
            $tamaraAppPayment->status = 'Failed';
            $tamaraAppPayment->save();
        }

        $lastPayment = $payment;
        // }

        // If nothing was found, return JSON error
        if (!$lastPayment) {
            return response()->json(['error' => 'No matching payment records found'], 404);
        }

        // Show result for the last processed failed payment
        $appointment = $lastPayment->appointment;
        $totalAmount = $lastPayment->amount;
        $priceWithoutTax = round($totalAmount / 1.15, 2);
        $taxAmount = round($totalAmount - $priceWithoutTax, 2);

        return view('Payment.result', [
            'status' => 'failed',
            'payment_type' => 'tamara',
            'payment' => $lastPayment,
            'phone' => $appointment->phone ?? $lastPayment->reference_id,
            'priceWithoutTax' => $priceWithoutTax,
            'taxAmount' => $taxAmount,
        ]);
    }


    public function cancel(Request $request)
    {
        $orderId = $request->query('orderId');
        $lastPayment = null;
        // Find payment for this sales line
        $payment = TamaraPayment::where('reference_id', $orderId)
            ->latest()
            ->first();

        if (!$payment) {
            // \Log::warning("TamaraPayment not found  for cancelled order {$orderId}");
            return response()->json(['error' => 'No matching payment records found'], 404);
        }

        // Update TamaraPayment status
        $payment->status = 'cancelled';
        $payment->save();

        // Update AppointmentPayment status
        $tamaraAppPayment = AppointmentPayment::where('payment_reference_id', $orderId)
            ->where('payment_type', 'tamara')
            ->latest()
            ->first();

        if ($tamaraAppPayment) {
            $tamaraAppPayment->status = 'Canceled';
            $tamaraAppPayment->save();
        }

        $lastPayment = $payment;


        if (!$lastPayment) {
            return response()->json(['error' => 'No matching payment records found'], 404);
        }

        // Prepare result for last processed cancellation
        $appointment = $lastPayment->appointment;
        $totalAmount = $lastPayment->amount; // includes 15% VAT
        $priceWithoutTax = round($totalAmount / 1.15, 2);
        $taxAmount = round($totalAmount - $priceWithoutTax, 2);

        return view('Payment.result', [
            'status' => 'canceled',
            'payment_type' => 'tamara',
            'payment' => $lastPayment,
            'phone' => $appointment->phone ?? $lastPayment->reference_id,
            'priceWithoutTax' => $priceWithoutTax,
            'taxAmount' => $taxAmount,
        ]);
    }

    // new integration functions

    public function newSuccess(Request $request)
    {
        $url = $request->path();
        preg_match('/reference_id=([^\/]+)/', $url, $match);
        $referenceId1 = $match[1] ?? null;
        $referenceId =  $referenceId1 ?? $request->query('reference_id');
        $orderId = $request->query('orderId');
        try {
            $sales_order_id = $request->sales_order_id ?? null;
            $directAppointment = DirectAppointment::where('sales_order_id', $sales_order_id)->orderByDesc('id')->first();

            $payment = DirectAppointmentPayment::where('reference_id', $referenceId)->first();

            if (!$payment) {
                return response()->json(['status' => false, 'message' => 'Payment not found']);
            }

            // ✅ Mark payment as paid
            $payment->update(['status' => 'paid']);
            $directAppointment->collect = max(0, $directAppointment->collect - $payment->price);
            $directAppointment->save();

            // ✅ Check if all payments for this sales_order_id are paid
            $paidSum = $directAppointment->payments()
                ->where('status', 'paid')
                ->sum('price');

            $discount = $directAppointment->discount ?? 0;
            $required = $directAppointment->required_amount ?? 0;

            // compare within small tolerance to handle floating-point decimals
            $allPaid = abs(($paidSum + $discount) - $required) < 0.01;

            if ($allPaid) {
                $appointment = DirectAppointment::where('sales_order_id', $payment->sales_order_id)->orderByDesc('id')->first();

                if ($appointment) {
                    // ✅ Update main appointment status
                    $appointment->update(['status' => 'paid']);

                    // ✅ Get all paid payments (including cash or POS)
                    $paidPayments = DirectAppointmentPayment::where('sales_order_id', $payment->sales_order_id)
                        ->whereIn('payment_type', ['cash', 'pos', 'tamara'])
                        ->where('status', 'paid')
                        ->get();

                    // ✅ Build sales lines from all paid payments
                    // Build salesLines manually
                    $discount = $appointment->discount ?? 0;

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
                            "worker" => $appointment->tech_id,
                            "SalesOrderId" => $appointment->sales_order_id,
                            "BookId" => $appointment->book_id,
                            "Discount" => $appointment->discount ?? 0,
                            "salesLines" => $salesLines,
                        ]
                    ];
                    Log::info("📤 Sending CompleteSuccessPayments for Direct Appointment ID {$appointment->id}: " . json_encode($body));
                    // check payments before sending to complete
                    $check = $this->checkCompleteService->checkPayments($appointment->book_id, $body);
                    if ($check['ok'] === true) {
                        // ✅ Run background artisan command
                        $cmd = "php " . escapeshellarg(base_path('artisan')) . " payments:complete "
                            . escapeshellarg($appointment->id) . " "
                            . escapeshellarg(json_encode($body))
                            . " > /dev/null 2>&1 &";

                        exec($cmd);
                    } else {
                        Log::warning('Payment completion blocked', [
                            'appointment_id' => $appointment->id,
                            'reason'         => $check['reason'],
                        ]);
                    }
                }
                Log::info("Sending CompleteSuccessPayments for Direct Appointment ID {$appointment->id}: " . json_encode($body));
            }

            // ✅ Calculate tax breakdown
            $totalAmount = $payment->price;
            $priceWithoutTax = round($totalAmount / 1.15, 2);
            $taxAmount = round($totalAmount - $priceWithoutTax, 2);

            try {
                // Call Tamara API for this order
                $statusResponse = app(TamaraService::class)->getOrderStatus($orderId);
                if (isset($statusResponse['status'])) {
                    if ($statusResponse['status'] === 'approved') {
                        $authResponse = app(TamaraService::class)->authorizeOrder($orderId);
                        // $captureResponse = app(TamaraService::class)->captureOrderNew($referenceId, $orderId);

                        if (isset($authResponse['status']) && $authResponse['status'] === 'authorised') {
                            $captureResponse = app(TamaraService::class)->captureOrderNew($referenceId, $orderId);
                        }
                    } elseif ($statusResponse['status'] === 'authorised') {
                        $captureResponse = app(TamaraService::class)->captureOrderNew($referenceId, $orderId);
                    }

                    // Mark success regardless of status flow

                }
            } catch (\Throwable $e) {
                Log::error('getAppointmentBySalesOrder failed: ' . $e->getMessage());
            }

            return view('Payment.result', [
                'status' => 'paid',
                'payment_type' => 'tamara',
                'payment' => $payment,
                'phone' => $payment->phone ?? $payment->reference_id,
                'priceWithoutTax' => $priceWithoutTax,
                'taxAmount' => $taxAmount,
            ]);
        } catch (\Throwable $e) {
            // Log::error('Tamara newSuccess error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }


    public function newFailure(Request $request)
    {
        // Get ALL TabbyPayment records for this appointment
        $payment = DirectAppointmentPayment::where('reference_id', $request->reference_id)->first();
        if ($payment) {
            $payment->status = 'failed';
            $payment->save();
        }
        $totalAmount = $payment->price;
        $priceWithoutTax = round($totalAmount / 1.15, 2);
        $taxAmount = round($totalAmount - $priceWithoutTax, 2);

        return view('Payment.result', [
            'status' => 'failed',
            'payment_type' => 'tamara',
            'payment' => $payment,
            'phone' => $payment->phone ?? $payment->reference_id,
            'priceWithoutTax' => $priceWithoutTax,
            'taxAmount' => $taxAmount,
        ]);
    }


    public function newCancel(Request $request)
    {
        // Get ALL TabbyPayment records for this appointment
        $payment = DirectAppointmentPayment::where('reference_id', $request->reference_id)->first();
        if ($payment) {
            $payment->status = 'failed';
            $payment->save();
        }
        $totalAmount = $payment->price;
        $priceWithoutTax = round($totalAmount / 1.15, 2);
        $taxAmount = round($totalAmount - $priceWithoutTax, 2);

        return view('Payment.result', [
            'status' => 'canceled',
            'payment_type' => 'tamara',
            'payment' => $payment,
            'phone' => $payment->phone ?? $payment->reference_id,
            'priceWithoutTax' => $priceWithoutTax,
            'taxAmount' => $taxAmount,
        ]);
    }



    public function webhook(Request $request)
    {
        $payload = $request->all();
        // Log::info('Tamara webhook received', $payload);

        $referenceId = $payload['order_reference_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$referenceId || !$status) {
            return response()->json(['error' => 'Missing data'], 400);
        }

        $payment = TamaraPayment::where('reference_id', $referenceId)->first();

        if ($payment) {
            $payment->status = strtolower($status);
            $payment->save();

            // Optionally update appointment status here
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required'
        ]);
        $import = new TamaraImport();
        Excel::import($import, $request->file('file'));
        return response()->json([
            'status' => true,
            'appointments' => $import->appointmentsList,
            'count' => count($import->appointmentsList)
        ]);
    }
    public function newNotification(Request $request)
    {
        $url = $request->path();
        preg_match('/reference_id=([^\/]+)/', $url, $match);
        $referenceId1 = $match[1] ?? null;

        $referenceId =  $referenceId1 ?? $request->query('reference_id');
        $parts = explode("sales_order_id=", $url);
        $sales_order_id = $parts[1] ?? null;

        try {
            $directAppointment = DirectAppointment::where('sales_order_id', $sales_order_id)->orderByDesc('id')->first();

            $payment = DirectAppointmentPayment::where('reference_id', $referenceId)->first();

            if (!$payment) {
                return response()->json(['status' => false, 'message' => 'Payment not found']);
            }

            // ✅ Mark payment as paid
            $payment->update(['status' => 'paid']);
            $directAppointment->collect = max(0, $directAppointment->collect - $payment->price);
            $directAppointment->save();

            // auth and capture process
            $orderId = $payment->payment_id;
            try {
                // Call Tamara API for this order
                $statusResponse = app(TamaraService::class)->getOrderStatus($orderId);
                if (isset($statusResponse['status'])) {
                    if ($statusResponse['status'] === 'approved') {
                        $authResponse = app(TamaraService::class)->authorizeOrder($orderId);
                        // $captureResponse = app(TamaraService::class)->captureOrderNew($referenceId, $orderId);

                        if (isset($authResponse['status']) && $authResponse['status'] === 'authorised') {
                            $captureResponse = app(TamaraService::class)->captureOrderNew($referenceId, $orderId);
                        }
                    } elseif ($statusResponse['status'] === 'authorised') {
                        $captureResponse = app(TamaraService::class)->captureOrderNew($referenceId, $orderId);
                    }

                    // Mark success regardless of status flow

                }
            } catch (\Throwable $e) {
                \Log::error('getAppointmentBySalesOrder failed: ' . $e->getMessage());
            }

            return response()->json(['status' => true, 'message' => 'Notification processed successfully']);
        } catch (\Throwable $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}
