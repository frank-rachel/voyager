<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class PointType extends Type
{
    public const NAME = 'point';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'point';
    }
}
