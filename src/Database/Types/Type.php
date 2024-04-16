<?php
namespace TCG\Voyager\Database\Types;

abstract class Type
{
    public const NAME = 'UNDEFINED_TYPE_NAME';

    // Define the properties each type class should have
    public const DBTYPE = 'default_db_type';
    protected $name;
    protected $category = 'Other';  // Default category

    public function __construct()
    {
        $this->name = static::NAME;
    }

    // Getter for name
    public function getName()
    {
        return $this->name;
    }

    // Getter for category
    public function getCategory()
    {
        return $this->category;
    }

    // Each type must implement its own SQL declaration method
    abstract public function getSQLDeclaration(array $field);

    // Convert the type instance to an array format for easy handling
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'category' => $this->getCategory()
        ];
    }

    // Method to initialize and return the type instance
    public static function getType($typeName)
    {
        $className = __NAMESPACE__ . '\\Postgresql\\' . $typeName;
        if (class_exists($className)) {
            return new $className();
        }
        throw new \Exception("Type $typeName does not exist.");
    }

    // Static method to register all types found in the specified directory
    public static function registerTypesFromDirectory($directory)
    {
        $types = [];
        foreach (glob($directory . '*.php') as $file) {
            $baseName = basename($file, '.php');
            $className = __NAMESPACE__ . '\\Postgresql\\' . $baseName;
            if (class_exists($className)) {
                $typeInstance = new $className();
                $types[$typeInstance->getName()] = $typeInstance->toArray();
            }
        }
        return $types;
    }
	

	
}

