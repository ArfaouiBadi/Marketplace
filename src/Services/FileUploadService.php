<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    private string $uploadsDirectory;

    public function __construct(
        string $projectDir,
        private SluggerInterface $slugger,
    ) {
        $this->uploadsDirectory = $projectDir . '/public/uploads/products';
    }

    public function uploadProductImage(UploadedFile $file): string
    {
        // Create uploads directory if it doesn't exist
        if (!is_dir($this->uploadsDirectory)) {
            mkdir($this->uploadsDirectory, 0755, true);
        }

        // Get and validate extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!$extension) {
            throw new \InvalidArgumentException('File must have a valid extension');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException(sprintf('File extension "%s" is not allowed. Allowed: %s', $extension, implode(', ', $allowedExtensions)));
        }

        // Generate a unique filename
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $filename = $safeFilename . '-' . uniqid() . '.' . $extension;

        // Move the file to the uploads directory
        $file->move($this->uploadsDirectory, $filename);

        return $filename;
    }
}
