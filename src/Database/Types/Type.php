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
    private const BUILTIN_TYPES_MAP = [
        Types::ASCII_STRING         => AsciiStringType::class,
        Types::BIGINT               => BigIntType::class,
        Types::BINARY               => BinaryType::class,
        Types::BLOB                 => BlobType::class,
        Types::BOOLEAN              => BooleanType::class,
        Types::DATE_MUTABLE         => DateType::class,
        Types::DATE_IMMUTABLE       => DateImmutableType::class,
        Types::DATEINTERVAL         => DateIntervalType::class,
        Types::DATETIME_MUTABLE     => DateTimeType::class,
        Types::DATETIME_IMMUTABLE   => DateTimeImmutableType::class,
        Types::DATETIMETZ_MUTABLE   => DateTimeTzType::class,
        Types::DATETIMETZ_IMMUTABLE => DateTimeTzImmutableType::class,
        Types::DECIMAL              => DecimalType::class,
        Types::FLOAT                => FloatType::class,
        Types::GUID                 => GuidType::class,
        Types::INTEGER              => IntegerType::class,
        Types::JSON                 => JsonType::class,
        Types::SIMPLE_ARRAY         => SimpleArrayType::class,
        Types::SMALLINT             => SmallIntType::class,
        Types::STRING               => StringType::class,
        Types::TEXT                 => TextType::class,
        Types::TIME_MUTABLE         => TimeType::class,
        Types::TIME_IMMUTABLE       => TimeImmutableType::class,
    ];
    private static ?TypeRegistry $typeRegistry = null;

    /** @internal Do not instantiate directly - use {@see Type::addType()} method instead. */
    final public function __construct()
    {
    }

    final public static function getTypeRegistry(): TypeRegistry
    {
        return self::$typeRegistry ??= self::createTypeRegistry();
    }

    private static function createTypeRegistry(): TypeRegistry
    {
        $instances = [];

        foreach (self::BUILTIN_TYPES_MAP as $name => $class) {
            $instances[$name] = new $class();
        }

        return new TypeRegistry($instances);
    }

    /**
     * Factory method to create type instances.
     *
     * @param string $name The name of the type.
     *
     * @throws Exception
     */
    public static function getType(string $name): self
    {
        return self::getTypeRegistry()->get($name);
    }

    /**
     * Finds a name for the given type.
     *
     * @throws Exception
     */
    public static function lookupName(self $type): string
    {
        return self::getTypeRegistry()->lookupName($type);
    }

    /**
     * Adds a custom type to the type map.
     *
     * @param string             $name      The name of the type.
     * @param class-string<Type> $className The class name of the custom type.
     *
     * @throws Exception
     */
    // public static function addType(string $name, string $className): void
    // {
        // self::getTypeRegistry()->register($name, new $className());
    // }

    /**
     * Checks if exists support for a type.
     *
     * @param string $name The name of the type.
     *
     * @return bool TRUE if type is supported; FALSE otherwise.
     */
    public static function hasType(string $name): bool
    {
        return self::getTypeRegistry()->has($name);
    }



	
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
