<?php

namespace TCG\Voyager\Database\Types\Postgresql;


use TCG\Voyager\Database\Types\Type;

class TimeTzType extends Type
{
    public const NAME = 'timetz';

    public function getSQLDeclaration(array $field)
    {
        return 'time(0) with time zone';
    }
}
