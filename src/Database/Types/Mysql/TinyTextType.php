<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class TinyTextType extends Type
{
    public const NAME = 'tinytext';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'tinytext';
    }
}
