<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RatingService
{
    public function getAllRatings(array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->with([
                'user',
                'item',
                'orderItem',
                'media'
            ])
            ->paginate($params['per_page'] ?? 15);
    }

    public function getRatingsByProduct($productId, array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->where('rating_type', 'product')
            ->where('item_id', $productId)
            ->with(['user', 'media'])
            ->paginate($params['per_page'] ?? 15);
    }

    public function getRatingsByService($serviceId, array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->where('rating_type', 'service')
            ->where('item_id', $serviceId)
            ->with(['user', 'media'])
            ->paginate($params['per_page'] ?? 15);
    }

    public function createRating(array $data)
    {
        return Rating::create($data);
    }

    protected function getRatingsQuery(array $params = []): Builder
    {
        $query = Rating::query();

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['sort_by']) && $params['sort_by'] === 'stars') {
            $query->orderBy('stars', $params['sort_direction'] ?? 'desc');
        } else {
            $query->latest();
        }
        return $query;
    }

    public function createRatingFromOrder(array $data, $userId)
    {
        // Tìm order item phù hợp
        $order = Order::whereHas('orderItems', function ($query) use ($data) {
            $query->where('item_type', $data['rating_type'])
                ->where('item_id', $data['item_id']);
        })
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->first();

        if (!$order) {
            throw new \Exception('Bạn chỉ có thể đánh giá sản phẩm/dịch vụ đã mua', 403);
        }

        // Lấy order item tương ứng
        $orderItem = $order->orderItems()
            ->where('item_type', $data['rating_type'])
            ->where('item_id', $data['item_id'])
            ->first();

        // Kiểm tra đánh giá đã tồn tại
        $existingRating = Rating::where([
            'user_id' => $userId,
            'order_item_id' => $orderItem->id,
            'rating_type' => $data['rating_type'],
            'item_id' => $data['item_id']
        ])->first();

        if ($existingRating) {
            throw new \Exception('Bạn đã đánh giá sản phẩm/dịch vụ này rồi', 403);
        }

        // Tạo rating mới với order_item_id
        return Rating::create(array_merge($data, [
            'user_id' => $userId,
            'order_item_id' => $orderItem->id,
            'status' => 'pending'
        ]));
    }

    public function createRatingFromOrderItem(array $data, OrderItem $orderItem)
    {
        try {
            DB::beginTransaction();

            // Tạo rating
            $rating = Rating::create([
                'user_id' => Auth::id(),
                'order_item_id' => $orderItem->id,
                'rating_type' => $orderItem->item_type,
                'item_id' => $orderItem->item_id,
                'stars' => $data['stars'],
                'comment' => $data['comment'] ?? null,
                'status' => 'pending'
            ]);

            // Upload và lưu ảnh nếu có
            if (!empty($data['images'])) {
                $mediaService = app(MediaService::class);
                $mediaService->createMultiple($rating, $data['images'], 'image');
            }

            DB::commit();
            return $rating->load('media');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRating(Rating $rating, array $data)
    {
        try {
            DB::beginTransaction();

            $rating->update([
                'stars' => $data['stars'] ?? $rating->stars,
                'comment' => $data['comment'] ?? $rating->comment,
            ]);

            // Xử lý ảnh mới nếu có
            if (!empty($data['images'])) {
                // Xóa ảnh cũ
                foreach ($rating->media as $media) {
                    app(MediaService::class)->delete($media);
                }

                // Upload ảnh mới
                $mediaService = app(MediaService::class);
                $mediaService->createMultiple($rating, $data['images'], 'image');
            }

            DB::commit();
            return $rating->fresh(['media']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveRating(Rating $rating)
    {
        return $rating->update([
            'status' => 'approved'
        ]);
    }

    public function rejectRating(Rating $rating)
    {
        return $rating->update([
            'status' => 'rejected'
        ]);
    }

    public function getApprovedRatings($type, $itemId, array $params = [])
    {
        return $this->getRatingsQuery($params)
            ->where('rating_type', $type)
            ->where('item_id', $itemId)
            ->where('status', 'approved')
            ->with(['user', 'media'])
            ->paginate($params['per_page'] ?? 15);
    }
}
