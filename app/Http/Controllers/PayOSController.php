<?php

namespace App\Http\Controllers;

use PayOS\PayOS;
use Illuminate\Http\Request;
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
            $orderCode = time(); // Hoặc có thể dùng mt_rand() để tạo số ngẫu nhiên
            
            // Validate input data
            $amount = intval($request->amount ?? 2000);
            $description = $request->description ?? 'Test payment';
            $returnUrl = $request->returnUrl;
            $cancelUrl = $request->cancelUrl;
            
            // Prepare payment data
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => $amount,
                'description' => $description,
                'returnUrl' => $returnUrl,
                'cancelUrl' => $cancelUrl,
            ];
            
            // Create signature
            $paymentData['signature'] = $this->createSignature($paymentData);
            
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
            $payOS = app(PayOS::class);
            $orderCode = $request->orderCode;
            
            // Kiểm tra trạng thái thanh toán
            $response = $payOS->getPaymentLinkInformation($orderCode);
            
            if ($response && $response['status'] === 'PAID') {
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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function createSignature($data)
    {
        try {
            // Sắp xếp các trường theo alphabet và format theo yêu cầu của PayOS
            $signData = sprintf(
                "amount=%d&cancelUrl=%s&description=%s&orderCode=%d&returnUrl=%s",
                $data['amount'],
                $data['cancelUrl'],
                $data['description'],
                $data['orderCode'],
                $data['returnUrl']
            );

            // Tạo signature với HMAC-SHA256
            return hash_hmac(
                'sha256',
                $signData,
                config('services.payos.checksum_key')
            );
        } catch (\Exception $e) {
            Log::error('Signature Creation Error:', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    private function verifySignature($response)
    {
        if (!isset($response['signature']) || !isset($response['data'])) {
            return false;
        }

        // Tạo signature từ response data
        $signData = json_encode($response['data']);
        $expectedSignature = hash_hmac(
            'sha256',
            $signData,
            config('services.payos.checksum_key')
        );

        return hash_equals($expectedSignature, $response['signature']);
    }
}
