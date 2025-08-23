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
        // Forzar siempre la URL de producción
        $baseUrl = 'https://mypetfinder.site';

        $reminderUrl = $this->urlGenerator->generate(
            'app_reminder_response',
            ['animalId' => $animal->getId(), 'type' => $type],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Reemplazar cualquier host con la URL de producción
        $reminderUrl = preg_replace('/https?:\/\/[^\/]+/', $baseUrl, $reminderUrl);

        $subject = $type === 'lost' ? '¿Sigue perdido tu ' . $animal->getAnimalType() . '?' : '¿Sigue activa tu publicación de ' . $animal->getAnimalType() . ' encontrado?';

        $email = (new Email())
            ->from($_ENV['MAIL_SENDER'] ?? 'noreply@mypetfinder.site')
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
        $icon = $type === 'lost' ? '🐕' : '🏠';
        $title = $type === 'lost' ? '¿Sigue perdido tu ' . $animalType . '?' : '¿Sigue activa tu publicación?';

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
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
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
                    background: #4CAF50;
                    color: white;
                    padding: 12px 30px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                    font-weight: bold;
                }
                .button:hover {
                    background: #45a049;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    color: #666;
                    font-size: 14px;
                }
                .info-box {
                    background: #e8f5e8;
                    border: 1px solid #4CAF50;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .warning-box {
                    background: #fff3cd;
                    border: 1px solid #ffc107;
                    color: #856404;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .animal-info {
                    background: #e3f2fd;
                    border: 1px solid #2196F3;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$icon} {$title}</h1>
                    <h2>Pet Finder - Recordatorio</h2>
                    <p>{$reminderText}</p>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$user->getFirstName()} {$user->getLastName()}</strong>,</p>

                    <p>{$message}</p>

                    <div class='animal-info'>
                        <h3>📋 Información de la publicación</h3>
                        <ul>
                            <li><strong>Animal:</strong> {$animalName}</li>
                            <li><strong>Tipo:</strong> {$animalType}</li>
                            <li><strong>Fecha de publicación:</strong> " . $animal->getCreatedAt()->format('d/m/Y') . "</li>
                            <li><strong>Estado actual:</strong> " . ucfirst(strtolower($animal->getStatus())) . "</li>
                        </ul>
                    </div>

                    <div class='info-box'>
                        <h3>🔄 ¿Qué puedes hacer?</h3>
                        <p>Actualiza el estado de tu publicación para mantener informada a la comunidad:</p>
                        <ul>
                            <li>✅ Si ya encontraste a tu mascota o al dueño</li>
                            <li>🔄 Si sigues buscando</li>
                            <li>📝 Si necesitas actualizar información</li>
                        </ul>
                    </div>

                    <div style='text-align: center;'>
                        <a href='{$reminderUrl}' class='button'>📝 Actualizar Estado</a>
                    </div>

                    <p>Si la publicación sigue activa, simplemente ignora este email y te recordaremos en otro mes.</p>

                    " . ($reminderCount >= 2 ? "<div class='warning-box'>
                        <h3>⚠️ Importante</h3>
                        <p>Este es tu <strong>{$reminderText}</strong>. Si no respondes, tu publicación será archivada automáticamente después del tercer recordatorio para mantener la información actualizada en nuestra plataforma.</p>
                        <p><strong>¿Por qué archivamos las publicaciones?</strong></p>
                        <ul>
                            <li>Mantener información actualizada para la comunidad</li>
                            <li>Evitar confusión con casos ya resueltos</li>
                            <li>Mejorar la eficiencia de búsquedas</li>
                        </ul>
                    </div>" : "") . "

                    <p><strong>¿Necesitas ayuda?</strong></p>
                    <p>Si tienes alguna pregunta o necesitas ayuda para actualizar tu publicación, no dudes en contactarnos.</p>

                    <p>¡Gracias por mantener actualizada la información y por ser parte de nuestra comunidad!</p>

                    <p>Saludos,<br>
                    <strong>El equipo de Pet Finder</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                    <p>© 2025 Pet Finder. Todos los derechos reservados.</p>
                </div>
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
