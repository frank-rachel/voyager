<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class TimeStampTzType extends Type
{
    public const NAME = 'timestamptz';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'timestamp(0) with time zone';
    }
}
