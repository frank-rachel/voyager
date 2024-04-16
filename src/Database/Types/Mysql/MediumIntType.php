<?php

namespace TCG\Voyager\Database\Types\Mysql;


use TCG\Voyager\Database\Types\Type;

class MediumIntType extends Type
{
    public const NAME = 'mediumint';

    public function getSQLDeclaration(array $field)
    {
        $commonIntegerTypeDeclaration = call_protected_method($platform, '_getCommonIntegerTypeDeclarationSQL', $field);

        return 'mediumint'.$commonIntegerTypeDeclaration;
    }
}
