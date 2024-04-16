<?php

namespace TCG\Voyager\Database\Types\Postgresql;


use TCG\Voyager\Database\Types\Type;

class InetType extends Type
{
    public const NAME = 'inet';

    public function getSQLDeclaration(array $field)
    {
        return 'inet';
    }
}
