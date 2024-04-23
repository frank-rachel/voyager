<?php

namespace TCG\Voyager\Database\Types;

use Illuminate\Database\Schema\Blueprint;

abstract class Type
{
    protected static $customTypesRegistered = false;
    protected static $allTypes = [];
    protected static $typeCategories = [];

    public const NAME = 'UNDEFINED_TYPE_NAME';

    // This method ensures that types are registered before they are needed
    public static function boot()
    {
        if (!static::$customTypesRegistered) {
            static::registerCustomPlatformTypes();
            static::$customTypesRegistered = true;
        }
    }

    // This method fetches all custom types for the specific platform and registers them
    protected static function registerCustomPlatformTypes()
    {
        $platformName = static::getPlatformName();
        $types = static::getPlatformCustomTypes($platformName);

        foreach ($types as $typeClass) {
            $typeInstance = new $typeClass();
            static::$allTypes[$typeInstance::NAME] = $typeClass;

            // Assuming that your Type classes implement a method to define how they should be added as a column
            Blueprint::macro($typeInstance::NAME, function ($column) use ($typeInstance) {
                // Example: This could set the column type, length, etc., based on the type definition
                return $this->addColumn($typeInstance::NAME, $column);
            });
        }
    }

    // Retrieves a type instance by name, throwing an exception if not found
    public static function getType($name)
    {
        static::boot();  // Ensure types are registered before fetching

        if (isset(static::$allTypes[$name])) {
            return new static::$allTypes[$name]();
        }
        throw new \Exception("Type not found: " . $name);
    }

    // Returns the name of the type
    public function getName()
    {
        return static::NAME;
    }

    // Helper method to get an array representation of a type, primarily for debugging
    public static function toArray(Type $type)
    {
        return ['name' => $type->getName()];
    }

    // Retrieves all registered types
    public static function getAllTypes()
    {
        return static::$allTypes;
    }

    // Fetches the custom types from the filesystem based on the specified platform
    protected static function getPlatformCustomTypes($platformName)
    {
        $typesPath = __DIR__ . DIRECTORY_SEPARATOR . $platformName . DIRECTORY_SEPARATOR;
        $namespace = __NAMESPACE__ . '\\' . $platformName . '\\';
        $types = [];

        foreach (glob($typesPath . '*.php') as $classFile) {
            $className = $namespace . basename($classFile, '.php');
            if (class_exists($className)) {
                $types[] = $className;
            } else {
                error_log("Class $className not found");
            }
        }
        return $types;
    }

    // Provides the platform name, which is used to determine the path for custom types
    protected static function getPlatformName()
    {
        return 'PostgreSQL';  // Adjust based on your application configuration
    }

    // Optional: Define type categories for further organization
    public static function getTypeCategories()
    {
        if (!empty(static::$typeCategories)) {
            return static::$typeCategories;
        }

        static::initializeTypeCategories();
        return static::$typeCategories;
    }

    // Initialize and store common type categories
    protected static function initializeTypeCategories()
    {
        static::$typeCategories = [
            'numbers' => ['boolean', 'integer', 'float', 'smallint', 'bigint', 'numeric'],
            'strings' => ['char', 'varchar', 'text'],
            'datetime' => ['date', 'timestamp', 'timestamptz', 'time', 'timetz', 'interval'],
            'json' => ['json', 'jsonb'],
            'arrays' => ['array'],
            'network' => ['cidr', 'inet', 'macaddr'],
        ];
    }
}
