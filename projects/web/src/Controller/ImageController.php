<?php

namespace App\Controller;

use App\Entity\AnimalPhotos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/images')]
final class ImageController extends AbstractController
{
   #[Route('/animal/{id}', name: 'app_image_animal', requirements: ['id' => '\d+'])]
   public function showAnimalImage(int $id, EntityManagerInterface $entityManager): Response
   {
      $photo = $entityManager->getRepository(AnimalPhotos::class)->find($id);

      if (!$photo) {
         throw $this->createNotFoundException('Imagen no encontrada');
      }

      // Construir la ruta correcta del archivo
      $filePath = $this->getParameter('app.uploads_dir') . '/../' . $photo->getFilePath();

      if (!file_exists($filePath)) {
         throw $this->createNotFoundException('Archivo de imagen no encontrado: ' . $filePath);
      }

      $response = new BinaryFileResponse($filePath);
      $response->setContentDisposition(
         ResponseHeaderBag::DISPOSITION_INLINE,
         $photo->getOriginalFilename()
      );

      return $response;
   }

   #[Route('/animal/{id}/download', name: 'app_image_animal_download', requirements: ['id' => '\d+'])]
   public function downloadAnimalImage(int $id, EntityManagerInterface $entityManager): Response
   {
      $photo = $entityManager->getRepository(AnimalPhotos::class)->find($id);

      if (!$photo) {
         throw $this->createNotFoundException('Imagen no encontrada');
      }

      // Construir la ruta correcta del archivo
      $filePath = $this->getParameter('app.uploads_dir') . '/../' . $photo->getFilePath();

      if (!file_exists($filePath)) {
         throw $this->createNotFoundException('Archivo de imagen no encontrado: ' . $filePath);
      }

      $response = new BinaryFileResponse($filePath);
      $response->setContentDisposition(
         ResponseHeaderBag::DISPOSITION_ATTACHMENT,
         $photo->getOriginalFilename()
      );

      return $response;
   }
}
