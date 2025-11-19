<?php
namespace App\Services;

class UploadService {
    public static function upload($file, $targetDir = null) {
        if ($file['error'] !== UPLOAD_ERR_OK) return false;

        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) return false;
        if ($file['size'] > 5 * 1024 * 1024) return false;

        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetDir = $targetDir ?? __DIR__ . '/../../public/uploads';
        $destination = $targetDir . '/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $newName;
        }
        return false;
    }
}
