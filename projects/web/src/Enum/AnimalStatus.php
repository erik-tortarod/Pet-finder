<?php

namespace App\Enum;

enum AnimalStatus: string
{
   case LOST = 'LOST';
   case FOUND = 'FOUND';
   case CLAIMED = 'CLAIMED';
   case FILLED = 'FILLED';

   public function getLabel(): string
   {
      return match ($this) {
         self::LOST => 'Perdido',
         self::FOUND => 'Encontrado',
         self::CLAIMED => 'Reclamado',
         self::FILLED => 'Completado',
      };
   }

   public static function fromString(string $value): ?self
   {
      return match ($value) {
         'LOST' => self::LOST,
         'FOUND' => self::FOUND,
         'CLAIMED' => self::CLAIMED,
         'FILLED' => self::FILLED,
         default => null,
      };
   }
}
