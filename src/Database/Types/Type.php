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
        'double'               => Common\DoubleType::class,
        'numeric'               => Common\NumericType::class,
        'timestamp'               => Postgresql\TimeStampType::class,
        'date'         			=> DateType::class,
        'integer'              => Common\IntegerType::class,
        'json'                 => Common\JsonType::class,
        'text'                 => Common\TextType::class,
    ];
    private static ?TypeRegistry $typeRegistry = null;

    final public function __construct()
    {
    }

    final public static function getTypeRegistry(): TypeRegistry
    {
        if (!self::$typeRegistry) {
            self::$typeRegistry = new TypeRegistry(self::BUILTIN_TYPES_MAP);
        }
        return self::$typeRegistry;
    }

    public static function getType(string $name): Type
    {
        return self::getTypeRegistry()->get($name);
    }

    /**
     * Instance method to convert a Type instance to an array.
     */
    public function toArray(): array
    {
        $category = self::determineCategory($this->name);
        $customOptions = self::$customTypeOptions[$this->name] ?? [];

        return array_merge([
            'name' => $this->name,
            'category' => $category,
        ], $customOptions);
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
        if (!static::$customTypesRegistered) {
            // Example: Register additional custom types dynamically if needed
            static::$customTypesRegistered = true;
        }
    }

    public static function getPlatformTypes()
    {
        self::registerCustomPlatformTypes(); // Make sure custom types are registered

        return collect(self::$allTypes)->mapWithKeys(function ($typeClass, $typeName) {
            return [$typeName => self::toArray($typeName)];
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
}

class TypeRegistry
{
    private $types;

    public function __construct(array $typeMap)
    {
        $this->types = collect($typeMap)->mapWithKeys(function ($typeClass, $typeName) {
            return [$typeName => new $typeClass()];
        });
    }

    public function get($name)
    {
        return $this->types->get($name);
    }

    public function has($name)
    {
        return $this->types->has($name);
    }

    public function add($name, $type)
    {
        $this->types[$name] = $type;
    }
}

