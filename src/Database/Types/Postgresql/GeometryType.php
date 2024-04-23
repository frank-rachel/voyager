<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class GeometryType extends Type
{
    public const NAME = 'geometry';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'geometry';
    }
}
