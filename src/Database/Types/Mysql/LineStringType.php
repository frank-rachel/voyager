<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class LineStringType extends Type
{
    public const NAME = 'linestring';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'linestring';
    }
}
