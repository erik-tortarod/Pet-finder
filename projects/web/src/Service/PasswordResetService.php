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
            ->from($_ENV['MAIL_SENDER'] ?? 'noreply@mypetfinder.site')
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
            <title>Restablecer Contraseña - Pet Finder</title>
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
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Restablecer Contraseña</h1>
                    <h2>Pet Finder</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$user->getFirstName()} {$user->getLastName()}</strong>,</p>

                    <p>Has solicitado restablecer tu contraseña en <strong>Pet Finder</strong>.</p>

                    <div class='info-box'>
                        <h3>🔑 ¿Qué necesitas hacer?</h3>
                        <p>Haz clic en el botón de abajo para crear una nueva contraseña segura para tu cuenta.</p>
                    </div>

                    <div style='text-align: center;'>
                        <a href='{$resetUrl}' class='button'>🔐 Restablecer Contraseña</a>
                    </div>

                    <div class='warning-box'>
                        <h3>⏰ Importante</h3>
                        <ul>
                            <li>Este enlace expirará en <strong>1 hora</strong> por razones de seguridad</li>
                            <li>Si no solicitaste este cambio, puedes ignorar este email</li>
                            <li>Tu contraseña actual permanecerá sin cambios hasta que completes el proceso</li>
                        </ul>
                    </div>

                    <p><strong>¿No solicitaste este cambio?</strong></p>
                    <p>Si no fuiste tú quien solicitó restablecer la contraseña, te recomendamos:</p>
                    <ul>
                        <li>🔒 Cambiar tu contraseña inmediatamente si puedes acceder a tu cuenta</li>
                        <li>📧 Contactar con nuestro equipo de soporte</li>
                        <li>⚠️ Revisar la seguridad de tu cuenta</li>
                    </ul>

                    <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

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
}
