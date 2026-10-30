<?php
declare(strict_types=1);

namespace Holy;

/** Saves an uploaded product photo after checking it really is an image. */
final class ImageUpload
{
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_BYTES = 5 * 1024 * 1024;

    /** @return array{path: ?string, error: ?string} path is relative to public/ */
    public static function save(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > self::MAX_BYTES) {
            return ['path' => null, 'error' => 'The image could not be uploaded (max 5 MB).'];
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime]) || !@getimagesize($file['tmp_name'])) {
            return ['path' => null, 'error' => 'Please upload a JPG, PNG or WebP image.'];
        }
        $dir = ROOT . '/public/uploads/products';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(8)) . '.' . self::TYPES[$mime]; // never trust the client's file name
        if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) {
            return ['path' => null, 'error' => 'The image could not be saved.'];
        }
        return ['path' => "uploads/products/$name", 'error' => null];
    }
}
