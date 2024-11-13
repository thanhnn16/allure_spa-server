<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\UserVoucher;

class VoucherService
{
    public function assignToUser(string $userId, int $voucherId, int $totalUses)
    {
        $voucher = Voucher::findOrFail($voucherId);

        // Check if voucher is active
        if ($voucher->status !== 'active') {
            throw new \Exception('Voucher không còn hoạt động');
        }

        // Check if voucher is expired
        if ($voucher->end_date < now()) {
            throw new \Exception('Voucher đã hết hạn');
        }

        // Check if voucher has reached usage limit
        if (!$voucher->is_unlimited && $voucher->used_times >= $voucher->usage_limit) {
            throw new \Exception('Voucher đã hết lượt sử dụng');
        }

        // Check if user has exceeded their usage limit
        $existingUses = UserVoucher::where('user_id', $userId)
            ->where('voucher_id', $voucherId)
            ->sum('total_uses');

        if ($existingUses + $totalUses > $voucher->uses_per_user) {
            throw new \Exception('Vượt quá số lần sử dụng cho phép của voucher này');
        }

        // Create or update user voucher
        $userVoucher = UserVoucher::updateOrCreate(
            [
                'user_id' => $userId,
                'voucher_id' => $voucherId,
            ],
            [
                'remaining_uses' => $totalUses,
                'total_uses' => $totalUses
            ]
        );

        return $userVoucher->load('voucher');
    }

    public function getUserVouchers(string $userId)
    {
        return UserVoucher::with('voucher')
            ->where('user_id', $userId)
            ->whereHas('voucher', function ($query) {
                $query->where('status', 'active')
                    ->where('end_date', '>', now());
            })
            ->where('remaining_uses', '>', 0)
            ->get();
    }
}
