<?php

namespace App\Http\Controllers;

use PayOS\PayOS;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            // Tạo orderCode ngẫu nhiên 6 số
            $orderCode = intval(substr(strval(microtime(true) * 10000), -6));
            
            // Chuẩn bị dữ liệu theo yêu cầu API
            $data = [
                "orderCode" => $orderCode,
                "amount" => 2000, // 2,000 VNĐ
                "description" => "Test PayOS Payment",
                "returnUrl" => $request->returnUrl,
                "cancelUrl" => $request->cancelUrl,
                // Thêm các trường tùy chọn
                "buyerName" => "Test User",
                "buyerEmail" => "test@example.com",
                "buyerPhone" => "0900000000",
                "buyerAddress" => "Test Address",
                "items" => [
                    [
                        "name" => "Test Payment",
                        "quantity" => 1,
                        "price" => 2000
                    ]
                ],
                // Unix timestamp cho thời gian hết hạn (1 giờ)
                "expiredAt" => time() + 3600,
            ];

            // Tạo signature theo yêu cầu API
            $data['signature'] = $this->createSignature($data);

            // Gọi API tạo payment link
            $response = $this->payOS->createPaymentLink($data);
            
            return response()->json([
                'success' => true,
                'checkoutUrl' => $response['data']['checkoutUrl'] ?? null,
                'qrCode' => $response['data']['qrCode'] ?? null,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            $orderCode = $request->orderCode;
            // Gọi API kiểm tra trạng thái payment
            $response = $this->payOS->getPaymentLinkInformation($orderCode);
            
            // Verify signature từ response
            if ($this->verifySignature($response)) {
                // Kiểm tra trạng thái thanh toán
                if ($response['data']['status'] === 'PAID') {
                    return response()->json([
                        'success' => true,
                        'data' => $response['data']
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
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
