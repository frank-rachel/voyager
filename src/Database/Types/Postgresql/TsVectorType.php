<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class TsVectorType extends Type
{
    public const NAME = 'tsvector';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'tsvector';
    }
}
