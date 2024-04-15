<?php
namespace App\Database\Types;

class TextType extends Type
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
