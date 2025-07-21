<?php

namespace App\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class AnimalStatusType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('LOST', 'FOUND', 'CLAIMED', 'FILLED', 'ARCHIVED')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value;
    }

    public function getName(): string
    {
        return 'animal_status_enum';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
