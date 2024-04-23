<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class MacAddrType extends Type
{
    public const NAME = 'macaddr';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'macaddr';
    }
}
