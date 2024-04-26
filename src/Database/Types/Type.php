<?php
namespace TCG\Voyager\Database\Types;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

abstract class Type
{
    protected static $customTypesRegistered = false;
    protected static $platformTypeMapping = [];
    protected static $allTypes = [];
    protected static $platformTypes = [];
    protected static $customTypeOptions = [];
    protected static $typeCategories = [];

    protected $name; // Instance variable to hold the type's name.

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
        'character varying'               => Common\VarCharType::class,
        'char'               => Common\CharType::class,
        'double'               => Common\DoubleType::class,
        'numeric'               => Common\NumericType::class,
        'timestamp'               => Postgresql\TimeStampType::class,
        'timestamp without time zone'               => Postgresql\TimeStampType::class,
        'date'         			=> DateType::class,
        'integer'              => Common\IntegerType::class,
        'json'                 => Common\JsonType::class,
        'text'                 => Common\TextType::class,
    ];
    private static ?TypeRegistry $typeRegistry = null;

    public function __construct($name) {
        $this->name = $name;
    }
	
    protected static function boot()
    {
        if (!static::$customTypesRegistered) {
            static::registerCustomPlatformTypes();
            static::$customTypesRegistered = true;
        }
    }
	
    final public static function getTypeRegistry(): TypeRegistry
    {
        if (!self::$typeRegistry) {
            self::$typeRegistry = new TypeRegistry(self::BUILTIN_TYPES_MAP);
        }
        return self::$typeRegistry;
    }

    private static function createTypeRegistry(): TypeRegistry
    {
        $instances = [];

        foreach (self::BUILTIN_TYPES_MAP as $name => $class) {
            $instances[$name] = new $class();
        }

        return new TypeRegistry($instances);
    }
	
    public static function getType(string $name): Type
    {
		// echo (" check name $name");
		// exit;
        return self::getTypeRegistry()->get($name);
    }

    /**
     * Method to convert a Type instance to an array.
     * Should be compatible across all subclasses.
     */
    public function toArray(Type $type)
    {
        $category = $this->determineCategory($type->name);
        $customOptions = $this->getCustomOptions($type->name);

        return [
            'name' => $type->name,
            'category' => $category,
            'customOptions' => $customOptions
        ];
    }

    private static function determineCategory($typeName): ?string
    {
        self::initializeTypeCategories();
        foreach (self::$typeCategories as $category => $types) {
            if (in_array($typeName, $types)) {
                return $category;
            }
        }
        return null;
    }

    public function getName(): string {
        return $this->name;
    }

    public static function registerCustomOption($name, $value, $types)
    {
        if (!is_array($types)) {
            $types = [$types];
        }
        foreach ($types as $type) {
            self::$customTypeOptions[$type][$name] = $value;
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

	public static function getPlatformTypes()
	{
		static::boot(); // Ensure types are registered

		return collect(static::$allTypes)->map(function ($typeClassName) {
			$typeInstance = new $typeClassName();  // Create an instance
			return $typeInstance->toArray($typeInstance);  // Call toArray on the instance
		})->groupBy('category');
	}
	
    public static function initializeTypeCategories()
    {
        if (!empty(self::$typeCategories)) {
            return;
        }

        self::$typeCategories = [
            'numbers' => ['bigint', 'integer', 'tinyint', 'smallint', 'mediumint', 'float', 'double', 'decimal'],
            'strings' => ['char', 'varchar', 'text'],
            'datetime' => ['date', 'datetime', 'timestamp'],
            'json' => ['json'],
            // Add more categories as necessary
        ];
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
}

class TypeRegistry
{
    private $types;

    public function __construct(array $typeMap)
    {
        $this->types = new Collection();
        foreach ($typeMap as $typeName => $typeClass) {
            if (class_exists($typeClass)) {
                $this->types[$typeName] = new $typeClass($typeName);
            }
        }
    }

    public function get($name): Type
    {
        if (!$this->types->has($name)) {
            throw new Exception("Type '{$name}' is not registered.");
        }
        return $this->types->get($name);
    }

    public function has($name)
    {
        return $this->types->has($name);
    }
}

