<?php
namespace TCG\Voyager\Database\Types;

class IntegerType extends Type
{
    // Default conversion to database value
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // Default implementation, override in child classes if needed
        return $value;
    }

    // Default conversion to PHP value
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Default implementation, override in child classes if needed
        return $value;
    }
}
