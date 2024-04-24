<?php

namespace TCG\Voyager\Database\Types;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Database\Types\Types;

abstract class Type
{
    protected static $customTypesRegistered = false;
    protected static $platformTypeMapping = [];
    protected static $allTypes = [];
    protected static $platformTypes = [];
    protected static $customTypeOptions = [];
    protected static $typeCategories = [];


    public const ASCII_STRING         = 'ascii_string';
    public const BIGINT               = 'bigint';
    public const BINARY               = 'binary';
    public const BLOB                 = 'blob';
    public const BOOLEAN              = 'boolean';
    public const DATE_MUTABLE         = 'date';
    public const DATE_IMMUTABLE       = 'date_immutable';
    public const DATEINTERVAL         = 'dateinterval';
    public const DATETIME_MUTABLE     = 'datetime';
    public const DATETIME_IMMUTABLE   = 'datetime_immutable';
    public const DATETIMETZ_MUTABLE   = 'datetimetz';
    public const DATETIMETZ_IMMUTABLE = 'datetimetz_immutable';
    public const DECIMAL              = 'decimal';
    public const FLOAT                = 'float';
    public const GUID                 = 'guid';
    public const INTEGER              = 'integer';
    public const JSON                 = 'json';
    public const SIMPLE_ARRAY         = 'simple_array';
    public const SMALLINT             = 'smallint';
    public const STRING               = 'string';
    public const TEXT                 = 'text';
    public const TIME_MUTABLE         = 'time';
    public const TIME_IMMUTABLE       = 'time_immutable';

    public const NAME = 'UNDEFINED_TYPE_NAME';
    public const NOT_SUPPORTED = 'notSupported';
    public const NOT_SUPPORT_INDEX = 'notSupportIndex';
    private const BUILTIN_TYPES_MAP = [
        'ascii_string'         => AsciiStringType::class,
        'bigint'               => Common\BigIntType::class,
        'varchar'               => Common\VarCharType::class,
        'char'               => Common\CharType::class,
        // 'integer'               => IntegerType::class,
        'double'               => Common\DoubleType::class,
        'numeric'               => Common\NumericType::class,
        // 'integer'               => IntegerType::class,
        // 'integer'               => Common\IntegerType::class,
        'timestamp'               => Postgresql\TimeStampType::class,
        // 'binary'               => BinaryType::class,
        // 'blob'                 => BlobType::class,
        // 'boolean'              => BooleanType::class,
        'date'         			=> DateType::class,
        // 'date_immutable'       => DateImmutableType::class,
        // 'dateinterval'         => DateIntervalType::class,
        // 'datetime'     			=> DateTimeType::class,
        // 'datetime_immutable'   => DateTimeImmutableType::class,
        // 'datetimetz'   			=> DateTimeTzType::class,
        // 'datetimetz_immutable' => DateTimeTzImmutableType::class,
        // 'decimal'              => DecimalType::class,
        // 'float'                => FloatType::class,
        // 'guid'                 => GuidType::class,
        'integer'              => Common\IntegerType::class,
        'json'                 => Common\JsonType::class,
        // 'simple_array'         => SimpleArrayType::class,
        // 'smallint'             => SmallIntType::class,
        // 'string'               => StringType::class,
        'text'                 => Common\TextType::class,
        // 'time'        			 => TimeType::class,
        // 'time_immutable'       => TimeImmutableType::class,
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
		// echo ("get type for $name");
		$type=self::getTypeRegistry()->get($name);
		// print_r($type);
		// exit;
        return $type;
    }

    public static function getTypeName($typeInstance): string
    {
        $className = get_class($typeInstance);
        foreach (self::BUILTIN_TYPES_MAP as $name => $class) {
            if ($class === $className) {
                return $name;
            }
        }
        throw new \Exception("Type not found for class instance: {$className}");
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


    public static function hasType(string $name): bool
    {
        return self::getTypeRegistry()->has($name);
    }


    public function getName(): string
    {
        return self::getTypeName($this);
    }
	
    // public static function toArray(Type $type)
    public function toArray(Type $type)
    {
        $customTypeOptions = $type->customOptions ?? [];
        return array_merge(['name' => $type->getName()], $customTypeOptions);
    }

	public static function getPlatformTypes()
	{
		static::boot(); // Ensure types are registered

		return collect(static::$allTypes)->map(function ($typeClassName) {
			$typeInstance = new $typeClassName();  // Create an instance
			return $typeInstance->toArray($typeInstance);  // Call toArray on the instance
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
