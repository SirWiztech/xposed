<?php
/**
 * XPOSED — File upload helper (admin content).
 * Whitelists image types, blocks execution, stores in /uploads.
 */

const ALLOWED_UPLOAD_TYPES = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
];

/**
 * Handle a file upload field. Returns site-relative path (e.g. "uploads/abc.jpg")
 * or null on failure. Throws with a message on invalid input.
 */
function handle_upload(string $field): ?string
{
    if (empty($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
        return null;
    }
    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error']);
    }
    if ($file['size'] > 4 * 1024 * 1024) {
        throw new RuntimeException('Upload too large — max 4MB.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!array_key_exists($ext, ALLOWED_UPLOAD_TYPES)) {
        throw new RuntimeException('Only JPG, PNG, WebP and GIF images are allowed.');
    }
    $mime = (function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : $file['type']);
    if ($mime !== ALLOWED_UPLOAD_TYPES[$ext]) {
        throw new RuntimeException('File type mismatch — re-export the image and try again.');
    }

    $dir = __DIR__ . '/../../uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save the upload.');
    }
    return 'uploads/' . $name;
}

/** Absolute-ish URL for an uploaded asset (honours the configured base). */
function upload_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
        return $path;
    }
    return url($path);
}
