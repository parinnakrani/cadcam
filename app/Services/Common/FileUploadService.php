<?php

namespace App\Services\Common;

use CodeIgniter\HTTP\Files\UploadedFile;

class FileUploadService
{
  /**
   * Upload an image file
   * 
   * @param UploadedFile $file
   * @param string $destination Sub-directory inside public/uploads/
   * @return string Relative path to the uploaded file
   * @throws \RuntimeException
   */
  public function uploadImage(UploadedFile $file, string $destination): string
  {
    if (!$file->isValid()) {
      throw new \RuntimeException($file->getErrorString());
    }

    // Validate type (JPG, PNG, GIF, WEBP)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime = $file->getMimeType();

    // Fallback check if mime type detection is weird, check extension
    if (!in_array($mime, $allowedTypes)) {
      // Basic extension check as backup
      $ext = strtolower($file->getExtension());
      if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        throw new \RuntimeException('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
      }
    }

    // Validate size (< 10 MB)
    if ($file->getSizeByUnit('mb') > 10) {
      throw new \RuntimeException('Image too large. Max 10MB.');
    }

    $newName = $this->generateRandomFilename($file->getName());

    // Ensure directory exists
    $uploadPath = FCPATH . 'uploads/' . $destination;
    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, true);
    }

    $file->move($uploadPath, $newName);

    return 'uploads/' . $destination . '/' . $newName;
  }

  /**
   * Upload a document file (PDF, DOC, XLS, etc.)
   */
  public function uploadDocument(UploadedFile $file, string $destination): string
  {
    if (!$file->isValid()) {
      throw new \RuntimeException($file->getErrorString());
    }

    // Validate size (< 20 MB)
    if ($file->getSizeByUnit('mb') > 20) {
      throw new \RuntimeException('File too large. Max 20MB.');
    }

    $newName = $this->generateRandomFilename($file->getName());

    $uploadPath = FCPATH . 'uploads/' . $destination;
    if (!is_dir($uploadPath)) {
      mkdir($uploadPath, 0755, true);
    }

    $file->move($uploadPath, $newName);

    return 'uploads/' . $destination . '/' . $newName;
  }

  /**
   * Delete a file from the server
   */
  public function deleteFile(string $filePath): bool
  {
    // filePath should be relative from FCPATH, e.g. 'uploads/products/image.jpg'
    $fullPath = FCPATH . $filePath;

    // Prevent directory traversal
    if (strpos($filePath, '..') !== false) {
      return false;
    }

    if (file_exists($fullPath) && is_file($fullPath)) {
      return unlink($fullPath);
    }
    return false;
  }

  private function generateRandomFilename(string $originalName): string
  {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    // Use simpler timestamp format for readability + random hex
    return date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  }
}
