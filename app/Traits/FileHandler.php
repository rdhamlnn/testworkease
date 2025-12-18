<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait FileHandler
{
    /**
     * Handle file upload.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory
     * @param  string|null  $oldFile
     * @return string|null
     */
    public function handleUpload($file, $directory = 'uploads', $oldFile = null)
    {
        if ($oldFile) {
            $this->deleteFile($oldFile);
        }

        return $file->store($directory, 'public');
    }

    /**
     * Delete file from storage.
     *
     * @param  string|null  $path
     * @return void
     */
    public function deleteFile($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
