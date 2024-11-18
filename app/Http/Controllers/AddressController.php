<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Cache;

class AddressController extends BaseController
{
    protected $addressService;

    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    /**
     * Store a new address
     * 
     * @OA\Post(
     *     path="/api/user/addresses",
     *     summary="Tạo địa chỉ mới",
     *     tags={"Address"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"province", "district", "address"},
     *             @OA\Property(property="province", type="string"),
     *             @OA\Property(property="district", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="address_type", type="string", enum={"home","work","shipping","others"}),
     *             @OA\Property(property="is_default", type="boolean"),
     *             @OA\Property(property="is_temporary", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Address")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'address_type' => 'required|in:home,work,shipping,others',
            'is_default' => 'boolean',
            'is_temporary' => 'boolean'
        ]);

        try {
            $validated['user_id'] = Auth::user()->id;

            $address = $this->addressService->create($validated);
            return $this->respondWithJson($address, 'Đã thêm địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, 'Lỗi khi thêm địa chỉ: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update an address
     * 
     * @OA\Put(
     *     path="/api/user/addresses/{address}",
     *     summary="Cập nhật địa chỉ",
     *     tags={"Address"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="province", type="string"),
     *             @OA\Property(property="district", type="string"),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="address_type", type="string", enum={"home","work","shipping","others"}),
     *             @OA\Property(property="is_default", type="boolean"),
     *             @OA\Property(property="is_temporary", type="boolean")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Address")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::user()->id) {
            return $this->respondWithJson(null, 'Không có quyền cập nhật địa chỉ này', 403);
        }

        $validated = $request->validate([
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'address_type' => 'required|in:home,work,shipping,others',
            'is_default' => 'boolean',
            'is_temporary' => 'boolean'
        ]);

        try {
            $validated['user_id'] = Auth::user()->id;

            $address = $this->addressService->update($address, $validated);
            return $this->respondWithJson($address, 'Đã cập nhật địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, 'Lỗi khi cập nhật địa chỉ: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete an address
     * 
     * @OA\Delete(
     *     path="/api/user/addresses/{address}",
     *     summary="Xóa địa chỉ",
     *     tags={"Address"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="address",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::user()->id) {
            return $this->respondWithJson(null, 'Không có quyền xóa địa chỉ này', 403);
        }

        try {
            $this->addressService->delete($address);
            return $this->respondWithJson(null, 'Đã xóa địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, 'Lỗi khi xóa địa chỉ: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all addresses for authenticated user
     * 
     * @OA\Get(
     *     path="/api/user/addresses",
     *     summary="Lấy danh sách địa chỉ",
     *     tags={"Address"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Address")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $addresses = $this->addressService->getAllForUser(Auth::user()->id);
            return $this->respondWithJson($addresses, 'Lấy danh sách địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, 'Lỗi khi lấy danh sách địa chỉ: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get addresses for current authenticated user
     * 
     * @OA\Get(
     *     path="/api/user/my-addresses",
     *     summary="Lấy danh sách địa chỉ của người dùng hiện tại",
     *     tags={"Address"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Lấy danh sách địa chỉ thành công"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="province", type="string", example="Hà Nội"),
     *                     @OA\Property(property="district", type="string", example="Cầu Giấy"),
     *                     @OA\Property(property="address", type="string", example="144 Xuân Thủy"),
     *                     @OA\Property(property="address_type", type="string", example="home"),
     *                     @OA\Property(property="is_default", type="boolean", example=true),
     *                     @OA\Property(property="is_temporary", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-15T09:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-15T09:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function getAddressByUser()
    {
        try {
            $addresses = $this->addressService->getAddressByUser();
            return $this->respondWithJson($addresses, 'Lấy danh sách địa chỉ thành công');
        } catch (\Exception $e) {
            return $this->respondWithJson(null, 'Lỗi khi lấy danh sách địa chỉ: ' . $e->getMessage(), 500);
        }
    }

    public function getProvinces()
    {
        try {
            return Cache::remember('provinces', 86400, function () {
                $response = Http::get('https://oapi.vn/api/provinces');
                return response()->json($response->json());
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDistricts($provinceCode)
    {
        try {
            return Cache::remember("districts_{$provinceCode}", 86400, function () use ($provinceCode) {
                $response = Http::get("https://oapi.vn/api/districts/{$provinceCode}");
                return response()->json($response->json());
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWards($districtCode)
    {
        try {
            return Cache::remember("wards_{$districtCode}", 86400, function () use ($districtCode) {
                $response = Http::get("https://oapi.vn/api/wards/{$districtCode}");
                return response()->json($response->json());
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
