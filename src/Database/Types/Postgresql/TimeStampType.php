<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class TimeStampType extends Type
{
    public const NAME = 'timestamp';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'timestamp(0) without time zone';
    }
}
