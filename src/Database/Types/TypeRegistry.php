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
    private static $types = [];
    private static $aliases = [  // Declare a new property for aliases
        'int' => 'integer',
        // 'bigint' => 'integer',
        // 'smallint' => 'integer',
        // 'tinyint' => 'integer',
        'double' => 'float',  // Example if needed
    ];

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
		$groupedTypes = collect($types)->mapWithKeys(function ($typeClass, $typeName) {
			$typeInstance = new $typeClass;
			return [$typeName => $typeInstance->toArray()];
		})->groupBy('category');

		// Safe retrieval of categories with fallback to an empty collection
		self::$platformTypes = [
			'Numbers' => $groupedTypes->get('Numeric', collect())->all(),
			'Strings' => $groupedTypes->get('String', collect())->all(),
			'Date and Time' => $groupedTypes->get('Date and Time', collect())->all(),
			'Other' => $groupedTypes->get('Other', collect())->all(),
			// Ensure all expected categories are covered, defaulting to an empty array if not present
		];

		// Optionally, handle uncategorized types:
		$knownCategories = ['Numeric', 'String', 'Date and Time', 'Other'];
		foreach ($groupedTypes as $category => $items) {
			if (!in_array($category, $knownCategories)) {
				// Append uncategorized items to the 'Other' category
				self::$platformTypes['Other'] = array_merge(self::$platformTypes['Other'], $items->all());
			}
		}

		return self::$platformTypes;
	}



    public static function getType($typeName)
    {
        if (!self::$customTypesRegistered) {
            self::registerCustomPlatformTypes();
        }

        // Check if the type name is an alias, and get the canonical type name
        $canonicalName = self::$aliases[$typeName] ?? $typeName;

        if (isset(self::$types[$canonicalName])) {
            return self::$types[$canonicalName];
        } else {
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
                self::$types[$typeInstance->getName()] = $typeInstance;
                Log::info("Registered type: " . $typeInstance->getName());
            }
        }
    }




    // private static function toArray(Type $type)
    // {
        // return [
            // 'name' => $type->getName(),
            // 'category' => $type->getCategory() // Assume these methods exist
        // ];
    // }

	private static function toArray(Type $type)
	{
		// Adjust this to include defaults based on type category or specific types
		$defaults = [];
		switch ($type->getCategory()) {
			case 'Numbers':
				$defaults = ['default' => ['type' => 'number', 'step' => 'any']];
				break;
			case 'Date and Time':
				if ($type->getName() === 'date') {
					$defaults = ['default' => ['type' => 'date']];
				} elseif (in_array($type->getName(), ['time', 'timetz'])) {
					$defaults = ['default' => ['type' => 'time', 'step' => '1']];
				}
				break;
			// Add other cases as needed
		}

		return array_merge([
			'name' => $type->getName(),
			'category' => $type->getCategory(),
		], $defaults);
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
        'bigint' => BigIntType::class,
        'integer' => IntegerType::class,
        'int' => IntegerType::class,

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




    public static function registerTypeCategories()
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
		

public static function logAvailableTypes()
{
    $typesArray = self::getPlatformTypes();

    // Checking and converting explicitly
    if ($typesArray instanceof Illuminate\Support\Collection) {
        $typesArray = $typesArray->toArray();
    }

    // Logging to check the type of $typesArray
    Log::info('TypesArray Type: ' . (is_array($typesArray) ? 'Array' : gettype($typesArray)));

    // Use array_keys on a guaranteed array
    Log::info("Available types: " . implode(", ", array_keys($typesArray)));
}
		
		
}
