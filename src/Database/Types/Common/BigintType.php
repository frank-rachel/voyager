<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class BigIntType extends Type
{
    public static function getName() {
        return 'bigint';
    }

    public function toArray() {
        return [
            'name' => self::getName(),
            'category' => 'Numbers'  // Assuming bigint falls under this category
        ];
    }
}
