<?php

namespace App\Service;

use App\Enum\AnimalStatus;

class AnimalStatusTranslationService
{
   private const TRANSLATIONS = [
      'es' => [
         AnimalStatus::LOST->value => 'Perdida',
         AnimalStatus::FOUND->value => 'Encontrada',
         AnimalStatus::CLAIMED->value => 'Reclamada',
         AnimalStatus::FILLED->value => 'Archivada',
      ],
      'en' => [
         AnimalStatus::LOST->value => 'Lost',
         AnimalStatus::FOUND->value => 'Found',
         AnimalStatus::CLAIMED->value => 'Claimed',
         AnimalStatus::FILLED->value => 'Archived',
      ]
   ];

   public function translate(string $status, string $locale = 'es'): string
   {
      return self::TRANSLATIONS[$locale][$status] ?? $status;
   }

   public function getAllTranslations(string $status): array
   {
      $translations = [];
      foreach (self::TRANSLATIONS as $locale => $statusTranslations) {
         $translations[$locale] = $statusTranslations[$status] ?? $status;
      }
      return $translations;
   }

   public function getAvailableLocales(): array
   {
      return array_keys(self::TRANSLATIONS);
   }

   public function getAvailableStatuses(): array
   {
      return array_keys(self::TRANSLATIONS['es']);
   }
}
