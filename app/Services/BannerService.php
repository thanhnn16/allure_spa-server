<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    public function getAllBanners()
    {
        return Banner::orderBy('created_at', 'desc')->get();
    }

    public function createBanner($data)
    {
        if (isset($data['image']) && $data['image']) {
            $path = $data['image']->store('banners', 'public');
            $data['image_url'] = $path;
        }

        return Banner::create($data);
    }

    public function updateBanner($id, $data)
    {
        $banner = Banner::findOrFail($id);

        if (isset($data['image']) && $data['image']) {
            if ($banner->image_url) {
                Storage::disk('public')->delete($banner->image_url);
            }
            
            $path = $data['image']->store('banners', 'public');
            $data['image_url'] = $path;
        }

        $banner->update($data);
        return $banner;
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        
        if ($banner->image_url) {
            Storage::disk('public')->delete($banner->image_url);
        }

        return $banner->delete();
    }
}
