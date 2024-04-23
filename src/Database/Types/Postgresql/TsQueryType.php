<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class TsQueryType extends Type
{
    public const NAME = 'tsquery';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'tsquery';
    }
}
