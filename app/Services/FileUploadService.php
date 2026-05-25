<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function storeAsset(UploadedFile $file, int $landingPageId, string $type): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid() . '.' . $extension;
        $path      = "landing-pages/{$landingPageId}/{$type}/{$filename}";

        Storage::disk('public')->putFileAs(
            "landing-pages/{$landingPageId}/{$type}",
            $file,
            $filename
        );

        return $path;
    }

    public function deleteAsset(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
