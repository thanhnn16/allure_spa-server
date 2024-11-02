<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentHistory;
use PayOS\PayOS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayOSController extends Controller
{
    protected $payOS;

    public function __construct(PayOS $payOS)
    {
        $this->payOS = $payOS;
    }

    public function testPayment(Request $request)
    {
        try {
            // Generate orderCode as number
            $orderCode = time();

            // Validate input data
            $amount = intval($request->amount ?? 2000);
            $description = $request->description ?? 'Test payment';
            $returnUrl = $request->returnUrl ?? config('app.url') . '/success'; // Add default value
            $cancelUrl = $request->cancelUrl ?? config('app.url') . '/cancel'; // Add default value

            // Prepare payment data
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => $amount,
                'description' => $description,
                'returnUrl' => $returnUrl,
                'cancelUrl' => $cancelUrl,
            ];

            // Log payment request for debugging
            Log::info('PayOS Request Data:', $paymentData);

            // Call PayOS API
            $response = $this->payOS->createPaymentLink($paymentData);

            // Log PayOS response for debugging
            Log::info('PayOS Response:', $response);

            if ($response && isset($response['checkoutUrl'])) {
                return response()->json([
                    'success' => true,
                    'checkoutUrl' => $response['checkoutUrl'],
                    'orderCode' => $orderCode
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo link thanh toán',
                'error' => $response['error'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('PayOS Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            $orderCode = $request->orderCode;
            $invoiceId = $request->invoice_id;
            
            if (!$orderCode || !$invoiceId) {
                throw new \Exception('Thông tin thanh toán không hợp lệ');
            }

            $invoice = Invoice::findOrFail($invoiceId);
            
            // Verify payment status with PayOS
            $response = $this->payOS->getPaymentLinkInformation($orderCode);
            
            if ($response && $response['status'] === 'PAID') {
                // Update invoice status
                DB::transaction(function () use ($invoice, $response) {
                    $oldStatus = $invoice->status;
                    $paidAmount = $response['amount'];
                    
                    // Update invoice paid amount and status
                    $invoice->paid_amount += $paidAmount;
                    $invoice->status = $invoice->paid_amount >= $invoice->total_amount ? 'paid' : 'partial';
                    $invoice->save();

                    // Create payment history
                    PaymentHistory::create([
                        'invoice_id' => $invoice->id,
                        'old_payment_status' => $oldStatus,
                        'new_payment_status' => $invoice->status,
                        'payment_amount' => $paidAmount,
                        'payment_method' => 'payos',
                        'created_by_user_id' => Auth::id(),
                        'note' => 'Thanh toán thành công qua PayOS',
                        'payment_data' => json_encode($response)
                    ]);
                });

                return response()->json([
                    'success' => true,
                    'data' => $response
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Thanh toán chưa hoàn tất'
            ]);
        } catch (\Exception $e) {
            Log::error('PayOS Verification Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
