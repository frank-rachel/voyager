<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class TimeTzType extends Type
{
    public const NAME = 'timetz';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'time(0) with time zone';
    }
}
