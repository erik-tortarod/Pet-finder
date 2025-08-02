<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Psr\Log\LoggerInterface;

#[AsEventListener(event: 'kernel.request', priority: 100)]
class LocaleListener
{
   public function __construct(private LoggerInterface $logger) {}

   public function onKernelRequest(RequestEvent $event): void
   {
      if (!$event->isMainRequest()) {
         return;
      }

      $request = $event->getRequest();

      // Establecer locale por defecto
      $locale = 'es';

      // 1. Intentar obtener el locale de la sesión (prioridad alta)
      if ($request->hasSession()) {
         $session = $request->getSession();
         $sessionLocale = $session->get('_locale');
         if ($sessionLocale) {
            $locale = $sessionLocale;
         }
      }

      // 2. Si no hay sesión, intentar obtener de la cookie (persistente)
      if ($locale === 'es' && $request->cookies->has('app_locale')) {
         $cookieLocale = $request->cookies->get('app_locale');
         if (in_array($cookieLocale, ['es', 'en'])) {
            $locale = $cookieLocale;
         }
      }

      // Log para debuggear
      $this->logger->info('LocaleListener: Setting locale', [
         'locale' => $locale,
         'session_id' => $request->hasSession() ? $request->getSession()->getId() : 'no_session',
         'cookie_locale' => $request->cookies->get('app_locale', 'not_set'),
         'source' => $request->hasSession() && $request->getSession()->get('_locale') ? 'session' : 'cookie'
      ]);

      // Establecer el locale en la request
      $request->setLocale($locale);
   }
}
