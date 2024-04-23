<?php

namespace TCG\Voyager\Database\Types;\Mysql;


use TCG\Voyager\Database\Types\Type;

class TinyIntType extends Type
{
    public const NAME = 'tinyint';

    public function getSQLDeclaration(array $field)
    {
        $commonIntegerTypeDeclaration = call_protected_method($platform, '_getCommonIntegerTypeDeclarationSQL', $field);

        return 'tinyint'.$commonIntegerTypeDeclaration;
    }
}
