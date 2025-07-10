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

   public function uploadAnimalPhoto(UploadedFile $file, Animals $animal, string $userEmail): AnimalPhotos
   {
      // Validar que el archivo sea válido
      if (!$file->isValid()) {
         throw new \InvalidArgumentException('El archivo subido no es válido: ' . $file->getErrorMessage());
      }

      // Validar que el archivo sea una imagen
      $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      $mimeType = $file->getMimeType();

      if (!in_array($mimeType, $allowedMimeTypes)) {
         throw new \InvalidArgumentException('Tipo de archivo no permitido. Solo se permiten imágenes (JPG, PNG, GIF, WebP)');
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
      $animalPhoto->setIsPrimary(true);

      return $animalPhoto;
   }

   public function getUploadsDir(): string
   {
      return $this->uploadsDir;
   }
}
