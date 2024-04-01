<?php

namespace TCG\Voyager\Database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use TCG\Voyager\Database\Types\Type;

class RealType extends Type
{
    public const NAME = 'real';
    public const DBTYPE = 'float4';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'real';
    }
}
