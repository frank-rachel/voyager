<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class XmlType extends Type
{
    public const NAME = 'xml';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'xml';
    }
}
