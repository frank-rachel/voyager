<?php
namespace TCG\Voyager\Database\Types;

class IntegerType extends Type
{
	public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
	{
		return "INTEGER";  // or "INTEGER", depending on your database dialect
	}
	
	
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

    public function getName(): string
    {
        return 'integer';  // Return the type name as a string
    }	
	
    public function getCategory(): string
    {
        return 'numeric';  // Categorize this type as numeric
    }	
}
