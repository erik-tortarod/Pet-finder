<?php

namespace App\Command;

use App\Entity\User;
use App\Service\PasswordResetService;
use App\Service\ReminderService;
use App\Service\ShelterEmailService;
use App\Entity\Animals;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
   name: 'app:test-emails',
   description: 'Envía emails de prueba de cada tipo para verificar estilos',
)]
class TestEmailsCommand extends Command
{
   public function __construct(
      private PasswordResetService $passwordResetService,
      private ReminderService $reminderService,
      private ShelterEmailService $shelterEmailService,
      private EntityManagerInterface $entityManager
   ) {
      parent::__construct();
   }

   protected function configure(): void
   {
      $this
         ->addOption(
            'email',
            'm',
            InputOption::VALUE_REQUIRED,
            'Email donde enviar las pruebas',
            'eriktortarod@gmail.com'
         )
         ->addOption(
            'type',
            't',
            InputOption::VALUE_OPTIONAL,
            'Tipo específico de email (reset, reminder-lost, reminder-found, shelter-approved, shelter-rejected, welcome, all)',
            'all'
         )
         ->setHelp('Este comando envía emails de prueba para verificar los estilos. Ejemplos:
                <info>php bin/console app:test-emails</info> - Envía todos los tipos de email
                <info>php bin/console app:test-emails --email=test@example.com</info> - Envía a email específico
                <info>php bin/console app:test-emails --type=reset</info> - Solo email de reset password
                <info>php bin/console app:test-emails --type=reminder-lost</info> - Solo recordatorio de mascota perdida
                <info>php bin/console app:test-emails --type=reminder-found</info> - Solo recordatorio de mascota encontrada
                <info>php bin/console app:test-emails --type=shelter-approved</info> - Solo email de protectora aprobada
                <info>php bin/console app:test-emails --type=shelter-rejected</info> - Solo email de protectora rechazada
                <info>php bin/console app:test-emails --type=welcome</info> - Solo email de bienvenida');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $email = $input->getOption('email');
      $type = $input->getOption('type');

      $io->title('🐾 Test de Emails - Pet Finder');
      $io->text("Enviando emails de prueba a: <info>{$email}</info>");

      // Crear usuario de prueba
      $testUser = $this->createTestUser($email);

      try {
         switch ($type) {
            case 'reset':
               $this->sendResetPasswordEmail($testUser, $io);
               break;
            case 'reminder-lost':
               $this->sendReminderEmail($testUser, 'lost', $io);
               break;
            case 'reminder-found':
               $this->sendReminderEmail($testUser, 'found', $io);
               break;
            case 'shelter-approved':
               $this->sendShelterApprovedEmail($testUser, $io);
               break;
            case 'shelter-rejected':
               $this->sendShelterRejectedEmail($testUser, $io);
               break;
            case 'welcome':
               $this->sendWelcomeEmail($testUser, $io);
               break;
            case 'all':
               $this->sendAllEmails($testUser, $io);
               break;
            default:
               $io->error("Tipo de email no válido: {$type}");
               $io->text('Tipos válidos: reset, reminder-lost, reminder-found, shelter-approved, shelter-rejected, welcome, all');
               return Command::FAILURE;
         }

         $io->success('¡Emails de prueba enviados correctamente!');
         $io->text('Revisa tu bandeja de entrada para ver los resultados.');

         return Command::SUCCESS;
      } catch (\Exception $e) {
         $io->error('Error al enviar emails: ' . $e->getMessage());
         return Command::FAILURE;
      }
   }

   private function createTestUser(string $email): User
   {
      $user = new User();
      $user->setEmail($email);
      $user->setFirstName('Usuario');
      $user->setLastName('Prueba');
      $user->setPassword('test123');
      $user->setRoles(['ROLE_USER']);
      $user->setEmailNotifications(true);
      $user->setCreatedAt(new \DateTimeImmutable());
      $user->setUpdatedAt(new \DateTimeImmutable());
      $user->setLastLogin(new \DateTime());
      $user->setIsActive(true);
      $user->setIsShelter(true);
      $user->setShelterName('Protectora de Prueba');
      $user->setShelterDescription('Esta es una protectora de prueba para testing');
      $user->setShelterAddress('Calle de Prueba 123, Ciudad');
      $user->setShelterPhone('+34 123 456 789');
      $user->setShelterWebsite('https://protectora-prueba.com');
      $user->setShelterFacebook('https://facebook.com/protectora-prueba');
      $user->setShelterVerificationStatus('PENDING');

      return $user;
   }

   private function sendResetPasswordEmail(User $user, SymfonyStyle $io): void
   {
      $io->text('📧 Enviando email de reset password...');

      // Crear un token de prueba
      $token = bin2hex(random_bytes(32));
      $resetUrl = "https://mypetfinder.site/auth/reset-password?token={$token}";

      // Usar el método privado del servicio mediante reflexión
      $reflection = new \ReflectionClass($this->passwordResetService);
      $method = $reflection->getMethod('getEmailTemplate');
      $method->setAccessible(true);

      $emailTemplate = $method->invoke($this->passwordResetService, $user, $resetUrl);

      // Enviar email usando el mailer directamente
      $this->sendTestEmail($user->getEmail(), 'Restablecer tu contraseña - Pet Finder', $emailTemplate);

      $io->text('✅ Email de reset password enviado');
   }

   private function sendReminderEmail(User $user, string $type, SymfonyStyle $io): void
   {
      $io->text("📧 Enviando email de recordatorio ({$type})...");

      // Crear animal de prueba
      $animal = new Animals();
      $animal->setName($type === 'lost' ? 'Luna' : 'Max');
      $animal->setAnimalType($type === 'lost' ? 'Perro' : 'Gato');
      $animal->setStatus('ACTIVE');
      $animal->setReminderCount(1);
      $animal->setCreatedAt(new \DateTimeImmutable('-2 months'));
      $animal->setUpdatedAt(new \DateTimeImmutable());

      $reminderUrl = "https://mypetfinder.site/reminder/response?animalId=123&type={$type}";

      // Usar el método privado del servicio mediante reflexión
      $reflection = new \ReflectionClass($this->reminderService);
      $method = $reflection->getMethod('getReminderEmailTemplate');
      $method->setAccessible(true);

      $emailTemplate = $method->invoke($this->reminderService, $user, $animal, $reminderUrl, $type);

      $subject = $type === 'lost'
         ? '¿Sigue perdido tu Perro?'
         : '¿Sigue activa tu publicación de Gato encontrado?';

      $this->sendTestEmail($user->getEmail(), $subject, $emailTemplate);

      $io->text("✅ Email de recordatorio ({$type}) enviado");
   }

   private function sendShelterApprovedEmail(User $user, SymfonyStyle $io): void
   {
      $io->text('📧 Enviando email de protectora aprobada...');

      $this->shelterEmailService->sendShelterApprovedEmail($user);

      $io->text('✅ Email de protectora aprobada enviado');
   }

   private function sendShelterRejectedEmail(User $user, SymfonyStyle $io): void
   {
      $io->text('📧 Enviando email de protectora rechazada...');

      $this->shelterEmailService->sendShelterRejectedEmail($user);

      $io->text('✅ Email de protectora rechazada enviado');
   }

   private function sendWelcomeEmail(User $user, SymfonyStyle $io): void
   {
      $io->text('📧 Enviando email de bienvenida...');

      $this->shelterEmailService->sendWelcomeEmail($user);

      $io->text('✅ Email de bienvenida enviado');
   }

   private function sendAllEmails(User $user, SymfonyStyle $io): void
   {
      $io->section('Enviando todos los tipos de email...');

      $this->sendResetPasswordEmail($user, $io);
      $this->sendReminderEmail($user, 'lost', $io);
      $this->sendReminderEmail($user, 'found', $io);
      $this->sendWelcomeEmail($user, $io);
      $this->sendShelterApprovedEmail($user, $io);
      $this->sendShelterRejectedEmail($user, $io);

      $io->text('🎉 Todos los emails han sido enviados');
   }

   private function sendTestEmail(string $to, string $subject, string $htmlContent): void
   {
      // Obtener el mailer del servicio
      $reflection = new \ReflectionClass($this->passwordResetService);
      $property = $reflection->getProperty('mailer');
      $property->setAccessible(true);
      $mailer = $property->getValue($this->passwordResetService);

      $email = new \Symfony\Component\Mime\Email();
      $email->from($_ENV['MAIL_SENDER'] ?? 'noreply@mypetfinder.site')
         ->to($to)
         ->subject($subject)
         ->html($htmlContent);

      $mailer->send($email);
   }
}
