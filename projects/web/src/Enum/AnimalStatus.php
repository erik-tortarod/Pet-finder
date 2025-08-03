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
         self::LOST => 'Perdida',
         self::FOUND => 'Encontrada',
         self::CLAIMED => 'Reclamada',
         self::FILLED => 'Archivada',
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
