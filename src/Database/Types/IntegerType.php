<?php
namespace App\Database\Types;

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
