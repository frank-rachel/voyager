<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class CidrType extends Type
{
    public const NAME = 'cidr';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'cidr';
    }
}
