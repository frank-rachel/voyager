<?php
namespace TCG\Voyager\Database\Types;

use Illuminate\Support\Facades\Log;

use TCG\Voyager\Database\Types\Common\{
    CharType, DoubleType, JsonType, NumericType, TextType, VarCharType, IntegerType, BigIntType
};
use TCG\Voyager\Database\Types\Postgresql\{
    BitType, BitVaryingType, ByteaType, CharacterType, CharacterVaryingType, CidrType, DoublePrecisionType,
    GeometryType, InetType, IntervalType, JsonbType, MacAddrType, MoneyType, RealType, SmallIntType,
    TimeStampType, TimeStampTzType, TimeTzType, TsQueryType, TsVectorType, TxidSnapshotType, UuidType, XmlType
};

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

	public static function getType($typeName)
	{
		if (!self::$customTypesRegistered) {
			self::registerCustomPlatformTypes();
		}

		// Ensure all parts of the collection are converted to arrays
		$typesArray = self::$platformTypes instanceof Illuminate\Support\Collection 
					  ? self::$platformTypes->mapWithKeys(function ($category) {
							return [$category => $category->toArray()];
						})->toArray() 
					  : self::$platformTypes;

		Log::debug("Final typesArray for usage", ['type' => gettype($typesArray), 'contents' => $typesArray]);

		if (isset($typesArray[$typeName])) {
			return new $typesArray[$typeName]();
		} else {
			// Ensure it's an array before logging or using array_keys()
			$typesArray = self::$platformTypes instanceof Illuminate\Support\Collection 
						  ? self::$platformTypes->mapWithKeys(function ($category) {
								return [$category => $category->toArray()];
							})->toArray() 
						  : self::$platformTypes;

			Log::info("Available types: " . implode(", ", array_keys($typesArray)));
			throw new \Exception("Type '{$typeName}' not found in TypeRegistry.");
		}
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
				$typeInstance = new $classNamespace();
				self::$platformTypes[$typeInstance->getName()] = $typeInstance;
				Log::info("Registered type: " . $typeInstance->getName());
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
	
    protected static function getPlatformCustomTypes($platformName)
    {
        $typesPath = __DIR__.DIRECTORY_SEPARATOR.$platformName.DIRECTORY_SEPARATOR;
        $namespace = __NAMESPACE__.'\\'.$platformName.'\\';
        $types = [];

        foreach (glob($typesPath.'*.php') as $classFile) {
            $types[] = $namespace.str_replace(
                '.php',
                '',
                str_replace($typesPath, '', $classFile)
            );
        }

        return $types;
    }

public static function getPlatformTypeMapping($platform)
{
    return [
        // Common types
        'char' => CharType::class,
        'double' => DoubleType::class,
        'json' => JsonType::class,
        'numeric' => NumericType::class,
        'text' => TextType::class,
        'varchar' => VarCharType::class,
        'integer' => IntegerType::class,
        'bigint' => BigIntType::class,

        // PostgreSQL types
        'bit' => BitType::class,
        'bit varying' => BitVaryingType::class,
        'bytea' => ByteaType::class,
        'character' => CharacterType::class,
        'character varying' => CharacterVaryingType::class,
        'cidr' => CidrType::class,
        'double precision' => DoublePrecisionType::class,
        'geometry' => GeometryType::class,
        'inet' => InetType::class,
        'interval' => IntervalType::class,
        'jsonb' => JsonbType::class,
        'macaddr' => MacAddrType::class,
        'money' => MoneyType::class,
        'real' => RealType::class,
        'smallint' => SmallIntType::class,
        'timestamp' => TimeStampType::class,
        'timestamptz' => TimeStampTzType::class,
        'timetz' => TimeTzType::class,
        'tsquery' => TsQueryType::class,
        'tsvector' => TsVectorType::class,
        'txid_snapshot' => TxidSnapshotType::class,
        'uuid' => UuidType::class,
        'xml' => XmlType::class
    ];
}




    protected static function registerTypeCategories()
    {
        $types = static::getTypeCategories();

        static::registerCustomOption('category', 'Numbers', $types['numbers']);
        static::registerCustomOption('category', 'Strings', $types['strings']);
        static::registerCustomOption('category', 'Date and Time', $types['datetime']);
        static::registerCustomOption('category', 'Lists', $types['lists']);
        static::registerCustomOption('category', 'Binary', $types['binary']);
        static::registerCustomOption('category', 'Geometry', $types['geometry']);
        static::registerCustomOption('category', 'Network', $types['network']);
        static::registerCustomOption('category', 'Objects', $types['objects']);
    }

    public static function getAllTypes()
    {
        if (static::$allTypes) {
            return static::$allTypes;
        }

        static::$allTypes = collect(static::getTypeCategories())->flatten();

        return static::$allTypes;
    }

    public static function getTypeCategories()
    {
        if (static::$typeCategories) {
            return static::$typeCategories;
        }

        $numbers = [
            'boolean',
            'tinyint',
            'smallint',
            'mediumint',
            'integer',
            'int',
            'bigint',
            'decimal',
            'numeric',
            'money',
            'float',
            'real',
            'double',
            'double precision',
        ];

        $strings = [
            'char',
            'character',
            'varchar',
            'character varying',
            'string',
            'guid',
            'uuid',
            'tinytext',
            'text',
            'mediumtext',
            'longtext',
            'tsquery',
            'tsvector',
            'xml',
        ];

        $datetime = [
            'date',
            'datetime',
            'year',
            'time',
            'timetz',
            'timestamp',
            'timestamptz',
            'datetimetz',
            'dateinterval',
            'interval',
        ];

        $lists = [
            'enum',
            'set',
            'simple_array',
            'array',
            'json',
            'jsonb',
            'json_array',
        ];

        $binary = [
            'bit',
            'bit varying',
            'binary',
            'varbinary',
            'tinyblob',
            'blob',
            'mediumblob',
            'longblob',
            'bytea',
        ];

        $network = [
            'cidr',
            'inet',
            'macaddr',
            'txid_snapshot',
        ];

        $geometry = [
            'geometry',
            'point',
            'linestring',
            'polygon',
            'multipoint',
            'multilinestring',
            'multipolygon',
            'geometrycollection',
        ];

        $objects = [
            'object',
        ];

        static::$typeCategories = [
            'numbers'  => $numbers,
            'strings'  => $strings,
            'datetime' => $datetime,
            'lists'    => $lists,
            'binary'   => $binary,
            'network'  => $network,
            'geometry' => $geometry,
            'objects'  => $objects,
        ];

        return static::$typeCategories;
    }
		
}
