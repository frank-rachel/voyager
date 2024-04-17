<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class IntegerType extends Type
{
    public const NAME = 'integer';

    // Optionally set a default category
    protected $category = 'Numeric';

    public function getSQLDeclaration(array $field)
    {
        // Handling precision and scale for numeric types
        // $precision = $field['precision'] ?? 10;  // Set default precision if not specified
        // $scale = $field['scale'] ?? 0;           // Set default scale if not specified

        return "integer";
    }

    // Override `toArray()` if additional properties are needed
    public function toArray()
    {
        return (parent::toArray());
    }
}

