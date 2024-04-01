<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class CidrType extends Type
{
    public const NAME = 'cidr';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'cidr';
    }
}
