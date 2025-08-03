<?php

namespace App\Twig;

use App\Service\AnimalStatusTranslationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnimalStatusExtension extends AbstractExtension
{
   public function __construct(
      private AnimalStatusTranslationService $translationService,
      private RequestStack $requestStack
   ) {}

   public function getFunctions(): array
   {
      return [
         new TwigFunction('animal_status_label', [$this, 'getStatusLabel']),
      ];
   }

   public function getStatusLabel(string $status): string
   {
      $request = $this->requestStack->getCurrentRequest();
      $locale = $request ? $request->getLocale() : 'es';
      return $this->translationService->translate($status, $locale);
   }
}
