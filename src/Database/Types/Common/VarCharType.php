<?php
namespace TCG\Voyager\Database\Types\Common;

use TCG\Voyager\Database\Types\Type;

class VarCharType extends Type
{
    public const NAME = 'string';
    // public const NAME = 'varchar';

    // Define a default category or use the one in the base class if appropriate
    protected $category = 'String';

    public function getSQLDeclaration(array $field)
    {
        $length = $field['length'] ?? 2000; // Default length
        return "VARCHAR($length)";
    }

    // You could override `toArray()` if additional properties are needed
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'length' => 2000, // Default length, adjust as necessary or make it dynamic based on $field
        ]);
    }
}
