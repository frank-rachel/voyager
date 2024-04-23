<?php

namespace TCG\Voyager\Database\Types;\Postgresql;

use TCG\Voyager\Database\Platforms\PostgreSQLPlatform;
use TCG\Voyager\Database\Types\Type;

class MoneyType extends Type
{
    public const NAME = 'money';

    public function getSQLDeclaration(array $field, PostgreSQLPlatform $platform)
    {
        return 'money';
    }
}
