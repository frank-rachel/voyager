<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class GeometryCollectionType extends Type
{
    public const NAME = 'geometrycollection';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'geometrycollection';
    }
}
