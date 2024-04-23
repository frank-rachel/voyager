<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class BigIntType extends Type
{
    public const NAME = 'bigint';

    // Optionally set a default category
    protected $category = 'Numeric';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform)
    {
        $field['length'] = empty($field['length']) ? 1 : $field['length'];

        return "bigint";
    }
}

