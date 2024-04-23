<?php

namespace TCG\Voyager\Database\Types;\Mysql;


use TCG\Voyager\Database\Types\Type;

class TinyBlobType extends Type
{
    public const NAME = 'tinyblob';

    public function getSQLDeclaration(array $field)
    {
        return 'tinyblob';
    }
}
