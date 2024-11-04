<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MediaController extends BaseController
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Delete media
     */
    public function destroy(Media $media)
    {
        $this->mediaService->delete($media);

        if (request()->expectsJson()) {
            return $this->respondWithJson(null, 'Media deleted successfully');
        }

        return back()->with('success', 'Media đã được xóa thành công.');
    }

    /**
     * Reorder media positions
     */
    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:media,id',
                'items.*.position' => 'required|integer|min:0'
            ]);

            Log::channel('media_debug')->info('Reordering media:', [
                'items' => $request->items
            ]);

            DB::beginTransaction();

            foreach ($request->items as $item) {
                Media::where('id', $item['id'])->update(['position' => $item['position']]);
            }

            DB::commit();

            Log::channel('media_debug')->info('Media reordered successfully');

            return $this->respondWithJson(null, 'Media reordered successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('media_debug')->error('Failed to reorder media:', [
                'error' => $e->getMessage()
            ]);
            return $this->respondWithError('Failed to reorder media: ' . $e->getMessage(), 500);
        }
    }
}
