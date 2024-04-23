<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class UuidType extends Type
{
    public const NAME = 'uuid';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'uuid';
    }
}
