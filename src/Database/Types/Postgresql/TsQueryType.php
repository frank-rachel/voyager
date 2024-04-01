<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class TsQueryType extends Type
{
    public const NAME = 'tsquery';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'tsquery';
    }
}
