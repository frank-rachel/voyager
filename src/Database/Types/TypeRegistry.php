<?php
namespace TCG\Voyager\Database\Types;
use TCG\Voyager\Database\Schema\SchemaManager;
use Composer\Autoload\ClassLoader;
use ReflectionClass;

class TypeRegistry
{
    private static $platformTypes = null;
    private static $customTypesRegistered = false;

    public static function getPlatformTypes()
    {
        if (self::$platformTypes) {
            return self::$platformTypes;
        }

        if (!self::$customTypesRegistered) {
            self::registerCustomPlatformTypes();
        }

        $platform = SchemaManager::getDatabasePlatform(); // Adjust according to actual usage

        $types = self::getPlatformTypeMapping($platform);
        self::$platformTypes = collect($types)->mapWithKeys(function ($typeClass, $typeName) {
            return [$typeName => self::toArray(new $typeClass)];
        })->groupBy('category');

        return self::$platformTypes;
    }

    private static function registerCustomPlatformTypes()
    {
        self::registerTypesFromDirectory(__DIR__ . '/Postgresql');
        self::registerTypesFromDirectory(__DIR__ . '/Common');
        self::$customTypesRegistered = true;
    }

    private static function registerTypesFromDirectory($directory)
    {
        foreach (glob($directory . "/*.php") as $file) {
            $className = basename($file, '.php');
            $classNamespace = 'TCG\\Voyager\\Database\\Types\\' . basename($directory) . '\\' . $className;
            if (class_exists($classNamespace)) {
                // Optionally initialize and register the type
                $typeInstance = new $classNamespace();
                self::$platformTypes[$typeInstance->getName()] = self::toArray($typeInstance);
            }
        }
    }

    private static function toArray(Type $type)
    {
        // Convert type instances to an array format if necessary
        // For example, returning type properties like name, category etc.
        return [
            'name' => $type->getName(),
            'category' => $type->getCategory() // Assume these methods exist
        ];
    }
}
