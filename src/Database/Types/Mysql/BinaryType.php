<?php

namespace FrankRachel\Voyager\Database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use FrankRachel\Voyager\Database\Types\Type;

class BinaryType extends Type
{
    public const NAME = 'binary';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        $field['length'] = empty($field['length']) ? 255 : $field['length'];

        return "binary({$field['length']})";
    }
}
