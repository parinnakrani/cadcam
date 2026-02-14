<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Exception;

/**
 * FileUploadService
 *
 * Handles file uploads securely.
 */
class FileUploadService
{
    /**
     * Upload a file to a specified directory.
     *
     * @param UploadedFile $file The file object
     * @param string $destinationPath Relative path from public root (e.g. 'uploads/products')
     * @param array $allowedTypes Array of allowed mime types or extensions
     * @return string The stored filename
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, string $destinationPath = 'uploads', array $allowedTypes = []): string
    {
        if (!$file->isValid()) {
            throw new Exception($file->getErrorString() . '(' . $file->getError() . ')');
        }

        // Validate type manually if needed, or rely on CI4 validation rules in controller.
        // Here we just move it.
        
        // Generate random name
        $newName = $file->getRandomName();

        // Ensure directory exists
        $uploadPath = FCPATH . $destinationPath;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if ($file->move($uploadPath, $newName)) {
            return $newName;
        }

        throw new Exception('Failed to move uploaded file.');
    }

    /**
     * Delete a file
     */
    public function deleteFile(string $path): bool
    {
        $fullPath = FCPATH . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
}
