<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class BigIntType extends Type
{
    public const NAME = 'bigint';

    // Optionally set a default category
    protected $category = 'Numeric';

    public function getName() {
        return self::NAME;  // Use the constant for consistency
    }

    public function toArray() {
        return [
            'name' => $this->getName(),  // Method call for consistency
            'category' => $this->category  // Correct property access
        ];
    }
}

