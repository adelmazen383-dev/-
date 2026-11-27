<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProviderDocumentService
{
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

    /**
     * Store an uploaded image or PDF securely on the private local disk with clean extension.
     */
    public static function store(UploadedFile $file, string $directory, string $prefix = 'doc'): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $ext = strtolower($file->guessExtension() ?: 'jpg');
        }

        $safeName = $prefix . '_' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(10)) . '.' . $ext;

        return $file->storeAs($directory, $safeName, 'local');
    }

    public static function isImage(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public static function isPdf(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    public static function resolveMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $extMap = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
        ];

        if (isset($extMap[$ext])) {
            return $extMap[$ext];
        }

        try {
            $detected = Storage::disk('local')->mimeType($path);
            if (is_string($detected) && $detected !== '' && $detected !== 'application/octet-stream') {
                return $detected;
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        return 'application/octet-stream';
    }

    /**
     * Stream an image or PDF inline (for browser/mobile viewing) or as a download when ?download=1.
     */
    public static function streamResponse(string $path, Request $request, ?string $friendlyBaseName = null): StreamedResponse
    {
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'الملف غير موجود.');
        }

        $mime        = self::resolveMimeType($path);
        $ext         = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'bin';
        $safeBase    = $friendlyBaseName
            ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $friendlyBaseName)
            : pathinfo($path, PATHINFO_FILENAME);
        $fileName    = trim($safeBase, '_') . '.' . $ext;
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        $headers = [
            'Content-Type'           => $mime,
            'Content-Disposition'    => "{$disposition}; filename=\"{$fileName}\"",
            'X-Content-Type-Options' => 'nosniff',
            'Accept-Ranges'          => 'bytes',
            'Cache-Control'          => 'private, max-age=3600',
        ];

        $size = Storage::disk('local')->size($path);
        if (is_int($size) && $size > 0) {
            $headers['Content-Length'] = (string) $size;
        }

        return Storage::disk('local')->response($path, $fileName, $headers);
    }
}
