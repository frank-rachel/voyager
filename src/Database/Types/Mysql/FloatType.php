<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class FloatType extends Type
{
    public const NAME = 'float';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'float';
    }
}
