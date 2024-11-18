<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BannerController extends BaseController
{
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * @OA\Get(
     *     path="/api/banners",
     *     summary="Get all active banners",
     *     tags={"Banners"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Banner")
     *         )
     *     )
     * )
     */
    public function index()
    {
        if (request()->wantsJson()) {
            $banners = $this->bannerService->getAllBanners();
            return $this->respondWithJson($banners);
        }

        return Inertia::render('MobileApp/Banners', [
            'banners' => $this->bannerService->getAllBanners(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/banners",
     *     summary="Create a new banner",
     *     tags={"Banners"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Banner")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Banner created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Banner")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $banner = $this->bannerService->createBanner($request->all());
        
        if ($request->wantsJson()) {
            return $this->respondWithJson($banner, 'Banner created successfully', 201);
        }
        
        return redirect()->route('banners.web')->with('success', 'Banner created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/banners/{id}",
     *     summary="Update a banner",
     *     tags={"Banners"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Banner")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Banner")
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $banner = $this->bannerService->updateBanner($id, $request->all());
        
        if ($request->wantsJson()) {
            return $this->respondWithJson($banner, 'Banner updated successfully');
        }
        
        return redirect()->route('banners.web')->with('success', 'Banner updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/banners/{id}",
     *     summary="Delete a banner",
     *     tags={"Banners"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->bannerService->deleteBanner($id);
        
        if (request()->wantsJson()) {
            return $this->respondWithJson(null, 'Banner deleted successfully');
        }
        
        return redirect()->route('banners.web')->with('success', 'Banner deleted successfully');
    }

    public function reorder(Request $request)
    {
        foreach ($request->orders as $order) {
            Banner::where('id', $order['id'])->update(['order' => $order['order']]);
        }
        return $this->respondWithJson(null, 'Order updated successfully');
    }
}
