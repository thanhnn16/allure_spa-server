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
            $payOS = app(PayOS::class);
            
            // Tạo mã đơn hàng ngẫu nhiên
            $orderCode = 'TEST' . time();
            
            // Tạo yêu cầu thanh toán
            $paymentData = [
                'orderCode' => $orderCode,
                'amount' => $request->amount ?? 2000,
                'description' => $request->description ?? 'Test payment',
                'returnUrl' => $request->returnUrl,
                'cancelUrl' => $request->cancelUrl,
                'signature' => '' // Tạo signature theo docs PayOS
            ];
            
            // Gọi API PayOS
            $response = $payOS->createPaymentLink($paymentData);
            
            if ($response && isset($response['checkoutUrl'])) {
                return response()->json([
                    'success' => true,
                    'checkoutUrl' => $response['checkoutUrl'],
                    'orderCode' => $orderCode
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo link thanh toán'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
