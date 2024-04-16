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

        $platform = SchemaManager::getDatabasePlatform();

        $types = self::getPlatformTypeMapping($platform);
        self::$platformTypes = collect($types)->mapWithKeys(function ($typeClass, $typeName) {
            return [$typeName => self::toArray(new $typeClass)];
        })->groupBy('category');

        return self::$platformTypes;
    }

    private static function registerCustomPlatformTypes()
    {
        $classLoader = require 'vendor/autoload.php';
        $allClasses = array_keys($classLoader->getClassMap());

        foreach ($allClasses as $class) {
            if (strpos($class, 'TCG\Voyager\Database\Types\Postgresql\\') === 0) {
                if (class_exists($class)) {
                    $typeInstance = new $class();
                    self::$platformTypes[$typeInstance->getName()] = self::toArray($typeInstance);
                }
            }
        }

        self::$customTypesRegistered = true;
    }

    private static function getPlatformTypeMapping($platformName)
    {
        // You would map platform-specific types here
        return [
            'integer' => IntegerType::class,
            'text' => TextType::class,
            // other types as necessary
        ];
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
