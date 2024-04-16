<?php

namespace TCG\Voyager\Database\Types\Common;


use TCG\Voyager\Database\Types\Type;

class JsonType extends Type
{
    public const NAME = 'json';

    public function getSQLDeclaration(array $field)
    {
        return 'json';
    }
}
