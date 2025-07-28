<?php

namespace App\Service;

use App\Entity\AnimalPhotos;
use App\Entity\Animals;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
   public function __construct(
      private string $uploadsDir,
      private SluggerInterface $slugger
   ) {}

   public function uploadAnimalPhotos(array $files, Animals $animal, string $userEmail): array
   {
      error_log('uploadAnimalPhotos called with ' . count($files) . ' files');

      if (empty($files)) {
         error_log('No files provided to uploadAnimalPhotos');
         return [];
      }

      $uploadedPhotos = [];
      $isFirstPhoto = true;

      foreach ($files as $index => $file) {
         error_log("Processing file $index: " . gettype($file));

         if ($file instanceof UploadedFile && $file->isValid()) {
            error_log("File $index is valid UploadedFile: " . $file->getClientOriginalName());
            try {
               $animalPhoto = $this->uploadSingleAnimalPhoto($file, $animal, $userEmail, $isFirstPhoto);
               $uploadedPhotos[] = $animalPhoto;
               $isFirstPhoto = false; // Solo la primera foto será principal
               error_log("Successfully uploaded file $index as " . ($animalPhoto->isPrimary() ? 'primary' : 'secondary'));
            } catch (\Exception $e) {
               // Log error pero continúa con las demás fotos
               error_log("Error uploading photo $index: " . $e->getMessage());
            }
         } else {
            error_log("File $index is not valid: " . ($file instanceof UploadedFile ? 'UploadedFile but invalid' : 'not UploadedFile'));
         }
      }

      error_log('uploadAnimalPhotos completed. Uploaded: ' . count($uploadedPhotos));
      return $uploadedPhotos;
   }

   private function uploadSingleAnimalPhoto(UploadedFile $file, Animals $animal, string $userEmail, bool $isPrimary = false): AnimalPhotos
   {
      // Validar que el archivo sea válido
      if (!$file->isValid()) {
         throw new \InvalidArgumentException('El archivo subido no es válido: ' . $file->getErrorMessage());
      }

      // Validar que el archivo sea una imagen
      $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
      $mimeType = $file->getMimeType();

      if (!in_array($mimeType, $allowedMimeTypes)) {
         throw new \InvalidArgumentException('Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WebP, AVIF)');
      }

      // Validar tamaño máximo (2MB)
      $maxSize = 2 * 1024 * 1024; // 2MB en bytes
      if ($file->getSize() > $maxSize) {
         throw new \InvalidArgumentException('El archivo es demasiado grande. El tamaño máximo es 2MB');
      }

      $userFolder = $this->slugger->slug($userEmail)->lower();
      $uploadDir = $this->uploadsDir . '/' . $userFolder;

      // Crear directorio si no existe
      if (!is_dir($uploadDir)) {
         if (!mkdir($uploadDir, 0755, true)) {
            throw new \RuntimeException('No se pudo crear el directorio de uploads');
         }
      }

      // Obtener propiedades del archivo ANTES de moverlo
      $originalFilename = $file->getClientOriginalName();
      $fileSize = $file->getSize();
      $extension = $file->guessExtension();

      $originalFilenameWithoutExt = pathinfo($originalFilename, PATHINFO_FILENAME);
      $safeFilename = $this->slugger->slug($originalFilenameWithoutExt);
      $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

      // Mover el archivo
      try {
         $file->move($uploadDir, $newFilename);
      } catch (\Exception $e) {
         throw new \RuntimeException('Error al mover el archivo: ' . $e->getMessage());
      }

      // Verificar que el archivo se movió correctamente
      $finalPath = $uploadDir . '/' . $newFilename;
      if (!file_exists($finalPath)) {
         throw new \RuntimeException('El archivo no se movió correctamente');
      }

      // Crear registro de foto
      $animalPhoto = new AnimalPhotos();
      $animalPhoto->setAnimalId($animal);
      $animalPhoto->setFilename($newFilename);
      $animalPhoto->setOriginalFilename($originalFilename);
      $animalPhoto->setFilePath('uploads/' . $userFolder . '/' . $newFilename);
      $animalPhoto->setFileSize($fileSize);
      $animalPhoto->setMimeType($mimeType);
      $animalPhoto->setCreatedAt(new \DateTimeImmutable());
      $animalPhoto->setIsPrimary($isPrimary);

      return $animalPhoto;
   }

   public function uploadAnimalPhoto(UploadedFile $file, Animals $animal, string $userEmail): AnimalPhotos
   {
      return $this->uploadSingleAnimalPhoto($file, $animal, $userEmail, true);
   }

   public function getUploadsDir(): string
   {
      return $this->uploadsDir;
   }
}
