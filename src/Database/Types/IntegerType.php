<?php
namespace TCG\Voyager\Database\Types;

class IntegerType extends Type
{
    public function convertToDatabaseValue($value)
    {
        return (int) $value;
    }

    public function convertToPHPValue($value)
    {
        return (int) $value;
    }
}
