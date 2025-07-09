<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class PasswordResetService
{
   public function __construct(
      private UserRepository $userRepository,
      private MailerInterface $mailer,
      private UrlGeneratorInterface $urlGenerator
   ) {}

   public function requestPasswordReset(string $email): bool
   {
      $user = $this->userRepository->findOneBy(['email' => $email]);

      if (!$user) {
         return false;
      }

      // Generar token único
      $token = bin2hex(random_bytes(32));
      $expiresAt = new \DateTime('+1 hour');

      // Guardar token en el usuario
      $user->setResetToken($token);
      $user->setResetTokenExpiresAt($expiresAt);
      $user->setResetTokenUsed(false);
      $user->setUpdatedAt(new \DateTimeImmutable());

      $this->userRepository->add($user, true);

      // Enviar email
      $this->sendPasswordResetEmail($user, $token);

      return true;
   }

   public function resetPassword(string $token, string $newPassword): bool
   {
      $user = $this->userRepository->findOneBy(['resetToken' => $token]);

      if (!$user) {
         return false;
      }

      // Verificar que el token no haya expirado
      if ($user->getResetTokenExpiresAt() < new \DateTime()) {
         return false;
      }

      // Verificar que el token no haya sido usado
      if ($user->isResetTokenUsed()) {
         return false;
      }

      // Actualizar contraseña
      $user->setPassword($newPassword);
      $user->setResetToken(null);
      $user->setResetTokenExpiresAt(null);
      $user->setResetTokenUsed(true);
      $user->setUpdatedAt(new \DateTimeImmutable());

      $this->userRepository->add($user, true);

      return true;
   }

   public function validateToken(string $token): ?User
   {
      $user = $this->userRepository->findOneBy(['resetToken' => $token]);

      if (!$user) {
         return null;
      }

      // Verificar que el token no haya expirado
      if ($user->getResetTokenExpiresAt() < new \DateTime()) {
         return null;
      }

      // Verificar que el token no haya sido usado
      if ($user->isResetTokenUsed()) {
         return null;
      }

      return $user;
   }

   private function sendPasswordResetEmail(User $user, string $token): void
   {
      $resetUrl = $this->urlGenerator->generate(
         'app_auth_reset_password',
         ['token' => $token],
         UrlGeneratorInterface::ABSOLUTE_URL
      );

      $email = (new Email())
         ->from($_ENV['MAIL_SENDER'] ?? 'noreply@petfinder.com')
         ->to($user->getEmail())
         ->subject('Restablecer tu contraseña - Pet Finder')
         ->html($this->getEmailTemplate($user, $resetUrl));

      $this->mailer->send($email);
   }

   private function getEmailTemplate(User $user, string $resetUrl): string
   {
      return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Restablecer Contraseña</title>
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
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Pet Finder</h1>
                <p>Restablecer Contraseña</p>
            </div>
            <div class='content'>
                <h2>Hola {$user->getFirstName()},</h2>
                <p>Has solicitado restablecer tu contraseña en Pet Finder.</p>
                <p>Haz clic en el botón de abajo para crear una nueva contraseña:</p>

                <a href='{$resetUrl}' class='button'>Restablecer Contraseña</a>

                <p>Si no solicitaste este cambio, puedes ignorar este email. Tu contraseña permanecerá sin cambios.</p>

                <p><strong>Importante:</strong> Este enlace expirará en 1 hora por razones de seguridad.</p>
            </div>
            <div class='footer'>
                <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                <p>Si tienes problemas, contacta con nuestro equipo de soporte.</p>
            </div>
        </body>
        </html>
        ";
   }
}
