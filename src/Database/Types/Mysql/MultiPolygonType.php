<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class MultiPolygonType extends Type
{
    public const NAME = 'multipolygon';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'multipolygon';
    }
}
