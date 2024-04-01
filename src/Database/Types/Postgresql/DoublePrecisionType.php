<?php

namespace FrankRachel\Voyager\Database\Types\Postgresql;

use FrankRachel\Voyager\Database\Types\Common\DoubleType;

class DoublePrecisionType extends DoubleType
{
    public const NAME = 'double precision';
    public const DBTYPE = 'float8';
}
