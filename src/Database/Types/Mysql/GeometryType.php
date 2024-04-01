<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class GeometryType extends Type
{
    public const NAME = 'geometry';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'geometry';
    }
}
