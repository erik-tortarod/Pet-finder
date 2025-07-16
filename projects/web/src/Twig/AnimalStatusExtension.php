<?php

namespace App\Twig;

use App\Service\AnimalStatusTranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnimalStatusExtension extends AbstractExtension
{
   public function __construct(
      private AnimalStatusTranslationService $translationService
   ) {}

   public function getFunctions(): array
   {
      return [
         new TwigFunction('animal_status_label', [$this, 'getStatusLabel']),
      ];
   }

   public function getStatusLabel(string $status, string $locale = 'es'): string
   {
      return $this->translationService->translate($status, $locale);
   }
}
