<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class MultiLineStringType extends Type
{
    public const NAME = 'multilinestring';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'multilinestring';
    }
}
