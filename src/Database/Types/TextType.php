<?php
namespace TCG\Voyager\Database\Types;

class TextType extends Type
{

	public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
	{
		return "TEXT"; 
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
        return 'text';
    }
	
    public function getCategory(): string
    {
        return 'text';  // Categorize this type as text
    }	
}
