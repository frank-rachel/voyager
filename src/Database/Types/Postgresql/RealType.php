<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class RealType extends Type
{
    public const NAME = 'real';
    public const DBTYPE = 'float4';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'real';
    }
}
