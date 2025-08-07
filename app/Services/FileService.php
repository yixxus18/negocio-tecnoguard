<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Log;


class FileService
{

    public static function uploadFile(UploadedFile $file, string $path = 'tickets'): array
    {
        try {

            $fileName = self::generateUniqueFileName($file);

            // Construir la ruta completa
            $fullPath = $path . '/' . $fileName;

            // Subir archivo
            $uploadedPath = Storage::disk('spaces')->putFileAs($path, $file, $fileName);
            $url = Storage::disk('spaces')->temporaryUrl(
                $fullPath,
                now()->addMinutes(10)
            );

            return [
                'success' => true,
                'path' => $uploadedPath,
                'url' => $url,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $fileName
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }


    private static function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return time() . '_' . uniqid() . '.' . $extension;
    }

    public static function getImageURL($name, $path = 'tickets')
    {
        try {
            $url = Storage::disk('spaces')->temporaryUrl(
                $path. '/' . $name ,
                now()->addMinutes(10)
            );
            return $url;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

}