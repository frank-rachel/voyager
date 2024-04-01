<?php

namespace FrankRachel\Voyager\Database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class JsonType extends Type
{
    public const NAME = 'json';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'json';
    }
}
