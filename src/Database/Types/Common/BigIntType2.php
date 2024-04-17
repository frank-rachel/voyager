<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class BigIntType extends Type
{
    public const NAME = 'bigint';

    // Optionally set a default category
    protected $category = 'Numeric';


    public function getName() {
        return 'bigint';
    }

    public function toArray() {
        return [
            'name' => self::getName(),
            'category' => $category  
        ];
    }
}
