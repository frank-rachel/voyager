<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class IntervalType extends Type
{
    public const NAME = 'interval';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'interval';
    }
}
