<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class ProductImage
{
    private const MAX_SIZE = 5242880;

    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @param array{name?: string, type?: string, tmp_name?: string, error?: int, size?: int} $upload
     */
    public static function save(array $upload): ?string
    {
        $error = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK || !isset($upload['tmp_name'], $upload['size'])) {
            throw new RuntimeException('Le téléversement de l’image a échoué.');
        }

        if ($upload['size'] < 1 || $upload['size'] > self::MAX_SIZE) {
            throw new RuntimeException('L’image ne doit pas dépasser 5 Mo.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        $extension = is_string($mimeType) ? (self::MIME_EXTENSIONS[$mimeType] ?? null) : null;
        if ($extension === null) {
            throw new RuntimeException('Formats acceptés : JPEG, PNG et WebP.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Impossible de préparer le stockage des images.');
        }

        if (!move_uploaded_file($upload['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
            throw new RuntimeException('Impossible d’enregistrer l’image du produit.');
        }

        return '/uploads/products/' . $filename;
    }

    public static function remove(?string $path): void
    {
        if ($path === null || preg_match('~^/uploads/products/[a-f0-9]{32}\.(jpg|png|webp)$~D', $path) !== 1) {
            return;
        }

        $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public'
            . str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (is_file($file) && !unlink($file)) {
            throw new RuntimeException('Impossible de supprimer l’image du produit.');
        }
    }
}
