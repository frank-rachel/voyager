<?php

namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class DoubleType extends Type
{
    public const NAME = 'double';

    // Define a default category or use the one in the base class if appropriate
    protected $category = 'Numeric';

    public function getSQLDeclaration(array $field)
    {
        // You might want to handle precision and scale if applicable
        $precision = $field['precision'] ?? null;
        $scale = $field['scale'] ?? null;
        
        $sql = "DOUBLE";
        if ($precision !== null && $scale !== null) {
            $sql .= " PRECISION($precision, $scale)";
        } elseif ($precision !== null) {
            $sql .= " PRECISION($precision)";
        }

        return $sql;
    }

    // You could override `toArray()` if additional properties are needed
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'precision' => null, // Default precision, adjust as necessary
            'scale' => null      // Default scale, adjust as necessary
        ]);
    }
}

