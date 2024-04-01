<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class MultiPointType extends Type
{
    public const NAME = 'multipoint';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'multipoint';
    }
}
