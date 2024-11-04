<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MediaService
{
    /**
     * Upload single file
     */
    public function upload(UploadedFile $file, string $folder = 'uploads'): string
    {
        try {
            $folderPath = 'images/' . $folder;
            
            $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folderPath, $fileName, 'public');
            
            Log::channel('media_debug')->info('File upload attempt:', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'folder' => $folderPath,
                'generated_name' => $fileName,
                'storage_path' => $path
            ]);

            if (!$path) {
                Log::channel('media_debug')->error('File upload failed', [
                    'file' => $file->getClientOriginalName(),
                    'folder' => $folderPath
                ]);
                throw new \Exception('Failed to store file');
            }

            return $path;
        } catch (\Exception $e) {
            Log::channel('media_debug')->error('File upload exception:', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'folder' => $folderPath ?? $folder
            ]);
            throw $e;
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(array $files, string $folder = 'uploads'): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->upload($file, $folder);
            }
        }
        return $paths;
    }

    /**
     * Create media record
     */
    public function create($model, UploadedFile $file, string $type = 'image'): Media
    {
        try {
            Log::channel('media_debug')->info('Creating media record:', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'file_name' => $file->getClientOriginalName(),
                'type' => $type
            ]);

            $folderName = strtolower(Str::plural(class_basename($model)));
            $path = $this->upload($file, $folderName);
            
            $media = $model->media()->create([
                'type' => $type,
                'file_path' => '/' . $path
            ]);

            Log::channel('media_debug')->info('Media record created:', [
                'media_id' => $media->id,
                'file_path' => $media->file_path,
                'full_url' => $media->getFullUrlAttribute()
            ]);

            return $media;
        } catch (\Exception $e) {
            Log::channel('media_debug')->error('Failed to create media:', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'model_type' => get_class($model),
                'model_id' => $model->id
            ]);
            throw $e;
        }
    }

    /**
     * Create multiple media records
     */
    public function createMultiple($model, array $files, string $type = 'image'): array
    {
        $mediaItems = [];
        Log::channel('media_debug')->info('Creating multiple media:', [
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'files_count' => count($files),
            'type' => $type
        ]);

        foreach ($files as $file) {
            try {
                if ($file instanceof UploadedFile) {
                    $mediaItems[] = $this->create($model, $file, $type);
                } else {
                    Log::channel('media_debug')->warning('Invalid file object:', [
                        'file_type' => gettype($file),
                        'file_class' => get_class($file)
                    ]);
                }
            } catch (\Exception $e) {
                Log::channel('media_debug')->error('Failed to create media item:', [
                    'error' => $e->getMessage(),
                    'file' => $file instanceof UploadedFile ? $file->getClientOriginalName() : 'Invalid file'
                ]);
            }
        }

        Log::channel('media_debug')->info('Media items created:', [
            'success_count' => count($mediaItems),
            'total_files' => count($files)
        ]);

        return $mediaItems;
    }

    /**
     * Delete media and its file
     */
    public function delete(Media $media): bool
    {
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }
        return $media->delete();
    }
}
