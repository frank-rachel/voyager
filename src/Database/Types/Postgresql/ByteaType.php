<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class ByteaType extends Type
{
    public const NAME = 'bytea';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'bytea';
    }
}
