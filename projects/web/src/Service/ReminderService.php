<?php

namespace App\Service;

use App\Entity\Animals;
use App\Entity\LostPets;
use App\Entity\FoundAnimals;
use App\Entity\User;
use App\Repository\AnimalsRepository;
use App\Repository\LostPetsRepository;
use App\Repository\FoundAnimalsRepository;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReminderService
{
   public function __construct(
      private AnimalsRepository $animalsRepository,
      private LostPetsRepository $lostPetsRepository,
      private FoundAnimalsRepository $foundAnimalsRepository,
      private UserRepository $userRepository,
      private MailerInterface $mailer,
      private UrlGeneratorInterface $urlGenerator,
      private \Doctrine\ORM\EntityManagerInterface $entityManager
   ) {}

   public function checkAndSendReminders(): void
   {
      // Buscar publicaciones que tengan más de 1 minuto (para testing)
      $cutoffDate = new \DateTimeImmutable('-1 month');

      // Buscar animales perdidos
      $lostPets = $this->lostPetsRepository->findOldPublications($cutoffDate);
      foreach ($lostPets as $lostPet) {
         $this->processReminder($lostPet->getUserId(), $lostPet->getAnimalId(), 'lost');
      }

      // Buscar animales encontrados
      $foundAnimals = $this->foundAnimalsRepository->findOldPublications($cutoffDate);
      foreach ($foundAnimals as $foundAnimal) {
         $this->processReminder($foundAnimal->getUserId(), $foundAnimal->getAnimalId(), 'found');
      }
   }

   private function processReminder(User $user, Animals $animal, string $type): void
   {
      $reminderCount = $animal->getReminderCount() ?? 0;

      // Si ya se han enviado 3 recordatorios sin respuesta, archivar automáticamente
      if ($reminderCount >= 3) {
         $animal->setStatus('ARCHIVED');
         $animal->setUpdatedAt(new \DateTimeImmutable());
         $this->entityManager->flush();
         return;
      }

      // Incrementar contador de recordatorios
      $animal->setReminderCount($reminderCount + 1);
      $animal->setUpdatedAt(new \DateTimeImmutable());
      $this->entityManager->flush();

      // Enviar email de recordatorio
      $this->sendReminderEmail($user, $animal, $type);
   }

   private function sendReminderEmail(User $user, Animals $animal, string $type): void
   {
      $reminderUrl = $this->urlGenerator->generate(
         'app_reminder_response',
         ['animalId' => $animal->getId(), 'type' => $type],
         UrlGeneratorInterface::ABSOLUTE_URL
      );

      $subject = $type === 'lost' ? '¿Sigue perdido tu ' . $animal->getAnimalType() . '?' : '¿Sigue activa tu publicación de ' . $animal->getAnimalType() . ' encontrado?';

      $email = (new Email())
         ->from($_ENV['MAIL_SENDER'] ?? 'noreply@petfinder.com')
         ->to($user->getEmail())
         ->subject($subject)
         ->html($this->getReminderEmailTemplate($user, $animal, $reminderUrl, $type));

      $this->mailer->send($email);
   }

   private function getReminderEmailTemplate(User $user, Animals $animal, string $reminderUrl, string $type): string
   {
      $animalType = $animal->getAnimalType();
      $animalName = $animal->getName() ?: $animalType;
      $reminderCount = $animal->getReminderCount();

      $message = $type === 'lost'
         ? "Hace más de un mes que publicaste que perdiste a <strong>{$animalName}</strong>. ¿Ya lo encontraste?"
         : "Hace más de un mes que publicaste que encontraste a <strong>{$animalName}</strong>. ¿Ya encontraste a su dueño?";

      $reminderText = $reminderCount === 1 ? "primer recordatorio" : "recordatorio #{$reminderCount}";

      return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recordatorio - Pet Finder</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background: #667eea;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    border-radius: 10px 10px 0 0;
                }
                .content {
                    background: #f9f9f9;
                    padding: 30px;
                    border-radius: 0 0 10px 10px;
                }
                .button {
                    display: inline-block;
                    background: #667eea;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .button:hover {
                    background: #5a6fd8;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 14px;
                    color: #666;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    color: #856404;
                    padding: 10px;
                    border-radius: 5px;
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Pet Finder</h1>
                <p>Recordatorio de Publicación ({$reminderText})</p>
            </div>
            <div class='content'>
                <h2>Hola {$user->getFirstName()},</h2>
                <p>{$message}</p>

                <p>Actualiza el estado de tu publicación haciendo clic en el botón de abajo:</p>

                <a href='{$reminderUrl}' class='button'>Actualizar Estado</a>

                <p>Si la publicación sigue activa, simplemente ignora este email y te recordaremos en otro mes.</p>

                " . ($reminderCount >= 2 ? "<div class='warning'><strong>Importante:</strong> Este es tu {$reminderText}. Si no respondes, tu publicación será archivada automáticamente después del tercer recordatorio.</div>" : "") . "
            </div>
            <div class='footer'>
                <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                <p>Si tienes problemas, contacta con nuestro equipo de soporte.</p>
            </div>
        </body>
        </html>
        ";
   }

   public function markAsResolved(int $animalId, string $type): bool
   {
      $animal = $this->animalsRepository->find($animalId);

      if (!$animal) {
         return false;
      }

      // Marcar como reclamado
      $animal->setStatus('CLAIMED');
      $animal->setUpdatedAt(new \DateTimeImmutable());

      $this->entityManager->flush();

      return true;
   }

   public function updateAnimalStatus(int $animalId, string $action, string $type): bool
   {
      $animal = $this->animalsRepository->find($animalId);

      if (!$animal) {
         return false;
      }

      switch ($action) {
         case 'still_searching':
            // Resetear contador de recordatorios cuando el usuario responde
            $animal->setReminderCount(0);
            break;

         case 'found':
            // Marcar como reclamado y resetear contador
            $animal->setStatus('CLAIMED');
            $animal->setReminderCount(0);
            break;

         case 'resolved':
            // Marcar como reclamado y resetear contador
            $animal->setStatus('CLAIMED');
            $animal->setReminderCount(0);
            break;

         default:
            return false;
      }

      $animal->setUpdatedAt(new \DateTimeImmutable());
      $this->entityManager->flush();

      return true;
   }
}
