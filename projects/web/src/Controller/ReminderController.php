<?php

namespace App\Controller;

use App\Service\ReminderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReminderController extends AbstractController
{
   public function __construct(
      private ReminderService $reminderService
   ) {}

   #[Route('/reminder/response/{animalId}/{type}', name: 'app_reminder_response')]
   public function reminderResponse(Request $request, int $animalId, string $type): Response
   {
      $action = $request->query->get('action');

      switch ($action) {
         case 'still_searching':
            $this->addFlash('info', 'Entendido, seguiremos mostrando tu publicación activa. Te recordaremos en otro mes.');
            break;

         case 'found':
            $success = $this->reminderService->updateAnimalStatus($animalId, $action, $type);
            if ($success) {
               $this->addFlash('success', '¡Excelente! Tu publicación ha sido marcada como resuelta. ¡Felicidades!');
            } else {
               $this->addFlash('error', 'No se pudo actualizar el estado de la publicación.');
            }
            break;

         case 'resolved':
            $success = $this->reminderService->updateAnimalStatus($animalId, $action, $type);
            if ($success) {
               $this->addFlash('success', 'Tu publicación ha sido marcada como resuelta. ¡Gracias por actualizar el estado!');
            } else {
               $this->addFlash('error', 'No se pudo actualizar el estado de la publicación.');
            }
            break;
      }

      return $this->render('reminder/response.html.twig', [
         'animalId' => $animalId,
         'type' => $type,
         'action' => $action
      ]);
   }
}
