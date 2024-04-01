<?php

namespace FrankRachel\Voyager\Database\Types\Sqlite;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class RealType extends Type
{
    public const NAME = 'real';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'real';
    }
}
