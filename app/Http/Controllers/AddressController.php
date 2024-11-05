<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

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
}
