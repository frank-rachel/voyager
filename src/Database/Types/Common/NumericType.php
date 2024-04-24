<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class NumericType extends Type
{
    public const NAME = 'numeric';

    // Optionally set a default category
    protected $category = 'Numeric';

    public function getSQLDeclaration(array $field)
    {
        // Handling precision and scale for numeric types
        $precision = $field['precision'] ?? 10;  // Set default precision if not specified
        $scale = $field['scale'] ?? 0;           // Set default scale if not specified

        return "NUMERIC($precision, $scale)";
    }

    // Override `toArray()` if additional properties are needed
    public function toArray(Type $type)
    {
        return array_merge(parent::toArray($type), [
            'precision' => 10,  // Default precision, adjust as necessary
            'scale' => 0        // Default scale, adjust as necessary
        ]);
    }
}

