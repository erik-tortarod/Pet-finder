<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class LocaleController extends AbstractController
{
   #[Route('/change-locale/{locale}', name: 'app_change_locale')]
   public function changeLocale(string $locale, Request $request, LoggerInterface $logger): Response
   {
      // Validar que el locale sea válido
      $availableLocales = ['es', 'en'];
      if (!in_array($locale, $availableLocales)) {
         $locale = 'es'; // Default to Spanish
      }

      // Log del cambio de idioma
      $logger->info('Changing locale', ['from' => $request->getLocale(), 'to' => $locale]);

      // Guardar el locale en la sesión
      $session = $request->getSession();
      $session->set('_locale', $locale);

      // Forzar el guardado de la sesión
      $session->save();

      // Crear respuesta con redirección
      $referer = $request->headers->get('referer');
      if ($referer) {
         $response = $this->redirect($referer);
      } else {
         $response = $this->redirectToRoute('app_home');
      }

      // Establecer cookie persistente para el idioma (1 año de duración)
      $response->headers->setCookie(
         new \Symfony\Component\HttpFoundation\Cookie(
            'app_locale',
            $locale,
            time() + (365 * 24 * 60 * 60), // 1 año
            '/',
            null,
            false, // secure
            true,  // httpOnly
            false, // raw
            'Lax'  // sameSite
         )
      );

      // Log para verificar que se guardó
      $logger->info('Locale saved in session and cookie', [
         'session_locale' => $session->get('_locale'),
         'cookie_locale' => $locale
      ]);

      return $response;
   }
}
