<?php

namespace App\Service;

use Symfony\Component\Filesystem\Exception\FileException as SymfonyFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class CvUploader
{

    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $targetDirectory = $this->targetDirectory;

        // Check if target directory exists, create it if not
        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }

        $targetFilePath = $targetDirectory . '/' . $fileName;

        // Check if file with the same name already exists, remove it
        if (file_exists($targetFilePath)) {
            unlink($targetFilePath);
        }

        try {
            $file->move($targetDirectory, $fileName);
        } catch (SymfonyFileException $e) {
            throw new \Exception('Unable to upload the file: ' . $e->getMessage());
        }

        return $fileName;
    }
}
