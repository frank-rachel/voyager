<?php
namespace TCG\Voyager\Database\Types;

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
        // Register your types here if any custom types need to be handled
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
