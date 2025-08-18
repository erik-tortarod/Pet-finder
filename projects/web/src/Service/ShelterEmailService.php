<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ShelterEmailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendShelterApprovedEmail(User $shelter): bool
    {
        try {
            $email = (new Email())
                ->from($_ENV['MAIL_SENDER'] ?? 'noreply@mypetfinder.site')
                ->to($shelter->getEmail())
                ->subject('¡Tu cuenta de protectora ha sido aprobada! - Pet Finder')
                ->html($this->getShelterApprovedEmailTemplate($shelter));

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            // Log error if needed
            return false;
        }
    }

    public function sendShelterRejectedEmail(User $shelter): bool
    {
        try {
            $email = (new Email())
                ->from($_ENV['MAIL_SENDER'] ?? 'noreply@mypetfinder.site')
                ->to($shelter->getEmail())
                ->subject('Información sobre tu solicitud de protectora - Pet Finder')
                ->html($this->getShelterRejectedEmailTemplate($shelter));

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            // Log error if needed
            return false;
        }
    }

    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $email = (new Email())
                ->from($_ENV['MAIL_SENDER'] ?? 'noreply@mypetfinder.site')
                ->to($user->getEmail())
                ->subject('¡Bienvenido a Pet Finder! - Tu cuenta ha sido creada')
                ->html($this->getWelcomeEmailTemplate($user));

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            // Log error if needed
            return false;
        }
    }

    private function getShelterApprovedEmailTemplate(User $shelter): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Cuenta Aprobada - Pet Finder</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 ¡Felicidades!</h1>
                    <h2>Tu cuenta de protectora ha sido aprobada</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$shelter->getFirstName()} {$shelter->getLastName()}</strong>,</p>

                    <p>Nos complace informarte que tu solicitud para registrarte como protectora <strong>{$shelter->getShelterName()}</strong> ha sido <strong>aprobada</strong> exitosamente.</p>

                    <p>Ahora puedes:</p>
                    <ul>
                        <li>✅ Publicar mascotas perdidas y encontradas</li>
                        <li>✅ Gestionar tu perfil de protectora</li>
                        <li>✅ Conectar con la comunidad</li>
                        <li>✅ Recibir notificaciones de casos cercanos</li>
                    </ul>

                    <div style='text-align: center;'>
                        <a href='https://mypetfinder.site/auth/login' class='button'>Iniciar Sesión</a>
                    </div>

                    <p><strong>Información de tu protectora:</strong></p>
                    <ul>
                        <li><strong>Nombre:</strong> {$shelter->getShelterName()}</li>
                        <li><strong>Email:</strong> {$shelter->getEmail()}</li>
                        <li><strong>Teléfono:</strong> " . ($shelter->getShelterPhone() ?: 'No especificado') . "</li>
                        <li><strong>Dirección:</strong> " . ($shelter->getShelterAddress() ?: 'No especificada') . "</li>
                    </ul>

                    <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

                    <p>¡Gracias por ser parte de nuestra comunidad!</p>

                    <p>Saludos,<br>
                    <strong>El equipo de Pet Finder</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                    <p>© 2025 Pet Finder. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getShelterRejectedEmailTemplate(User $shelter): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Información sobre tu solicitud - Pet Finder</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .info-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📋 Información importante</h1>
                    <h2>Sobre tu solicitud de protectora</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$shelter->getFirstName()} {$shelter->getLastName()}</strong>,</p>

                    <p>Lamentamos informarte que tu solicitud para registrarte como protectora <strong>{$shelter->getShelterName()}</strong> no pudo ser aprobada en esta ocasión.</p>

                    <div class='info-box'>
                        <p><strong>¿Por qué puede haber sido rechazada?</strong></p>
                        <ul>
                            <li>Información incompleta o incorrecta</li>
                            <li>No se pudo verificar la legitimidad de la protectora</li>
                            <li>Documentación insuficiente</li>
                            <li>No cumple con nuestros criterios de verificación</li>
                        </ul>
                    </div>

                    <p><strong>¿Qué puedes hacer?</strong></p>
                    <ul>
                        <li>📧 Contactar con nuestro equipo de soporte para obtener más información</li>
                        <li>🔄 Volver a solicitar el registro con información actualizada</li>
                        <li>📝 Asegurarte de proporcionar toda la documentación necesaria</li>
                    </ul>

                    <div style='text-align: center;'>
                        <a href='mailto:mypetfinder.website@gmail.com' class='button'>Contactar Soporte</a>
                    </div>

                    <p><strong>Información de tu solicitud:</strong></p>
                    <ul>
                        <li><strong>Nombre de la protectora:</strong> {$shelter->getShelterName()}</li>
                        <li><strong>Email:</strong> {$shelter->getEmail()}</li>
                        <li><strong>Fecha de solicitud:</strong> " . $shelter->getCreatedAt()->format('d/m/Y H:i:s') . "</li>
                    </ul>

                    <p>Si consideras que esto es un error o tienes preguntas, no dudes en contactarnos. Estamos aquí para ayudarte.</p>

                    <p>Saludos,<br>
                    <strong>El equipo de Pet Finder</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                    <p>© 2025 Pet Finder. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function getWelcomeEmailTemplate(User $user): string
    {
        $isShelter = $user->isShelter();
        $shelterName = $user->getShelterName();

        $template = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>¡Bienvenido a Pet Finder!</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #4CAF50; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .info-box { background: #e8f5e8; border: 1px solid #4CAF50; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .shelter-info { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🐾 ¡Bienvenido a Pet Finder!</h1>
                    <h2>Tu cuenta ha sido creada exitosamente</h2>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$user->getFirstName()} {$user->getLastName()}</strong>,</p>

                    <p>¡Nos complace darte la bienvenida a <strong>Pet Finder</strong>! Tu cuenta ha sido creada exitosamente y ya puedes comenzar a usar nuestra plataforma.</p>

                    <div class='info-box'>
                        <h3>🎯 ¿Qué puedes hacer ahora?</h3>
                        <ul>
                            <li>✅ Explorar mascotas perdidas y encontradas en tu zona</li>
                            <li>✅ Publicar mascotas perdidas o encontradas</li>
                            <li>✅ Contactar directamente con otros usuarios</li>
                            <li>✅ Recibir notificaciones de casos cercanos</li>
                            <li>✅ Gestionar tu perfil y preferencias</li>
                        </ul>
                    </div>";

        if ($isShelter) {
            $template .= "
                    <div class='shelter-info'>
                        <h3>🏠 Información de tu Protectora</h3>
                        <p><strong>Nombre:</strong> {$shelterName}</p>
                        <p><strong>Estado:</strong> Pendiente de verificación</p>
                        <p>Nuestro equipo revisará tu información y te notificaremos cuando tu cuenta sea verificada. Este proceso suele tomar entre 24-48 horas hábiles.</p>
                        <p>Una vez verificada, podrás:</p>
                        <ul>
                            <li>✅ Publicar mascotas bajo protección de tu shelter</li>
                            <li>✅ Gestionar el perfil completo de tu protectora</li>
                            <li>✅ Recibir notificaciones prioritarias</li>
                            <li>✅ Acceder a herramientas especiales para shelters</li>
                        </ul>
                    </div>";
        }

        $template .= "
                    <div style='text-align: center;'>
                        <a href='https://mypetfinder.site/auth/login' class='button'>Iniciar Sesión</a>
                    </div>

                    <p><strong>Información de tu cuenta:</strong></p>
                    <ul>
                        <li><strong>Email:</strong> {$user->getEmail()}</li>
                        <li><strong>Nombre:</strong> {$user->getFirstName()} {$user->getLastName()}</li>
                        <li><strong>Teléfono:</strong> " . ($user->getPhone() ?: 'No especificado') . "</li>
                        <li><strong>Fecha de registro:</strong> " . $user->getCreatedAt()->format('d/m/Y H:i:s') . "</li>
                    </ul>

                    <p><strong>¿Necesitas ayuda?</strong></p>
                    <p>Si tienes alguna pregunta o necesitas ayuda para usar la plataforma, no dudes en contactarnos. Estamos aquí para ayudarte.</p>

                    <p>¡Gracias por unirte a nuestra comunidad y por ayudar a reunir familias con sus mascotas!</p>

                    <p>Saludos,<br>
                    <strong>El equipo de Pet Finder</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                    <p>© 2025 Pet Finder. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";

        return $template;
    }
}
