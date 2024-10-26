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
            Log::info('Starting PayOS test payment');
            
            // Tạo orderCode là số nguyên dương không quá 9007199254740991
            $orderCode = mt_rand(1000000, 9999999); // 7 chữ số
            
            // Chuẩn bị dữ liệu thanh toán
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => 2000,
                'description' => 'Test PayOS Payment',
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                // Thêm các thông tin tùy chọn
                'cancelUrl' => $request->cancelUrl,
                'returnUrl' => $request->returnUrl,
                'expiredAt' => time() + 3600, // Hết hạn sau 1 giờ
                'items' => [
                    [
                        'name' => 'Test Payment',
                        'quantity' => 1,
                        'price' => 2000
                    ]
                ]
            ];

            Log::info('Payment data prepared', ['data' => $paymentData]);

            // Tạo payment link
            $response = $this->payOS->createPaymentLink($paymentData);
            
            Log::info('PayOS response received', ['response' => $response]);

            return response()->json([
                'success' => true,
                'checkoutUrl' => $response['checkoutUrl'] ?? null,
                'orderCode' => $orderCode
            ]);

        } catch (\Exception $e) {
            Log::error('PayOS test payment error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            Log::info('Verifying PayOS payment', [
                'orderCode' => $request->orderCode
            ]);

            $response = $this->payOS->getPaymentLinkInformation(
                intval($request->orderCode)
            );

            Log::info('PayOS verification response', [
                'response' => $response
            ]);

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('PayOS verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function createSignature($data)
    {
        // Sắp xếp các trường theo alphabet
        $signData = "amount={$data['amount']}&cancelUrl={$data['cancelUrl']}&description={$data['description']}&orderCode={$data['orderCode']}&returnUrl={$data['returnUrl']}";
        
        // Tạo signature với HMAC-SHA256
        return hash_hmac(
            'sha256',
            $signData,
            config('services.payos.checksum_key')
        );
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
