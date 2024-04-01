<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class InetType extends Type
{
    public const NAME = 'inet';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'inet';
    }
}
