<?php

namespace TCG\Voyager\Database\Types;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class Type
{
    protected static $customTypesRegistered = false;
    protected static $platformTypeMapping = [];
    protected static $allTypes = [];
    protected static $platformTypes = [];
    protected static $customTypeOptions = [];
    protected static $typeCategories = [];

    public const NAME = 'UNDEFINED_TYPE_NAME';
    public const NOT_SUPPORTED = 'notSupported';
    public const NOT_SUPPORT_INDEX = 'notSupportIndex';

    public function getName()
    {
        return static::NAME;
    }

    public static function toArray(Type $type)
    {
        $customTypeOptions = $type->customOptions ?? [];
        return array_merge(['name' => $type->getName()], $customTypeOptions);
    }

    public static function getPlatformTypes()
    {
        static::boot(); // Ensure types are registered

        return collect(static::$allTypes)->map(function ($type) {
            return static::toArray(new $type());
        })->groupBy('category');
    }

    protected static function boot()
    {
        if (!static::$customTypesRegistered) {
            static::registerCustomPlatformTypes();
            static::$customTypesRegistered = true;
        }
    }

    protected static function registerCustomPlatformTypes()
    {
        $platformName = static::getPlatformName();
        $customTypes = array_merge(
            static::getPlatformCustomTypes('Common'),
            static::getPlatformCustomTypes($platformName)
        );

        foreach ($customTypes as $typeClass) {
            $name = $typeClass::NAME;
            static::addType($name, $typeClass);
        }
        
        static::addCustomTypeOptions($platformName);
    }

    protected static function getPlatformCustomTypes($platformName)
    {
        $typesPath = __DIR__ . DIRECTORY_SEPARATOR . $platformName . DIRECTORY_SEPARATOR;
        $namespace = __NAMESPACE__ . '\\' . $platformName . '\\';
        $types = [];

        foreach (glob($typesPath . '*.php') as $classFile) {
            $className = $namespace . basename($classFile, '.php');
            if (class_exists($className)) {
                $types[] = $className;
            }
        }
        return $types;
    }

    protected static function getPlatformName()
    {
        // You can make this dynamic or configuration driven as needed
        return 'PostgreSQL';
    }

    public static function addType($name, $typeClass)
    {
        static::$allTypes[$name] = $typeClass;
    }

    protected static function addCustomTypeOptions($platformName)
    {
        // You would implement your own logic here to add custom options
        // For demonstration, let's assume a simple setup
        static::$customTypeOptions[$platformName] = [
            // Define some custom options as needed
        ];
    }

    public static function getTypeCategories()
    {
        if (empty(static::$typeCategories)) {
            static::initializeTypeCategories();
        }
        return static::$typeCategories;
    }

    protected static function initializeTypeCategories()
    {
        static::$typeCategories = [
            'numbers' => [
                'boolean', 'tinyint', 'smallint', 'mediumint', 'integer', 'bigint',
                'decimal', 'numeric', 'money', 'float', 'real', 'double', 'double precision'
            ],
            'strings' => [
                'char', 'character', 'varchar', 'string', 'text'
            ],
            'datetime' => [
                'date', 'datetime', 'timestamp'
            ],
            // More categories as required...
        ];
    }

    public static function getAllTypes()
    {
        return static::$allTypes;
    }
}
