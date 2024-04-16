<?php
namespace TCG\Voyager\Database\Types;

use TCG\Voyager\Database\Types\Common\{
    CharType, DoubleType, JsonType, NumericType, TextType, VarCharType
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
    // This assumes all types are defined in either the Common or Postgresql directories.
    // It uses the list of type names you provided to map each type to its corresponding class.
    return [
        // Numeric types
        'boolean' => BooleanType::class,
        'tinyint' => TinyIntType::class,
        'smallint' => SmallIntType::class,
        'mediumint' => MediumIntType::class,  // Assuming naming convention follows type names
        'integer' => IntegerType::class,
        'int' => IntegerType::class,
        'bigint' => BigIntType::class,
        'decimal' => DecimalType::class,
        'numeric' => NumericType::class,
        'money' => MoneyType::class,
        'float' => FloatType::class,
        'real' => RealType::class,
        'double' => DoubleType::class,
        'double precision' => DoubleType::class,

        // String types
        'char' => CharType::class,
        'character' => CharacterType::class,
        'varchar' => VarCharType::class,
        'character varying' => CharacterVaryingType::class,
        'string' => StringType::class,
        'guid' => GuidType::class,  // Assuming GUID and UUID types are represented as such
        'uuid' => UuidType::class,
        'tinytext' => TextType::class,
        'text' => TextType::class,
        'mediumtext' => TextType::class,
        'longtext' => TextType::class,
        'tsquery' => TsQueryType::class,
        'tsvector' => TsVectorType::class,
        'xml' => XmlType::class,

        // Date and Time types
        'date' => DateType::class,
        'datetime' => DateTimeType::class,
        'year' => YearType::class,
        'time' => TimeType::class,
        'timetz' => TimeTzType::class,
        'timestamp' => TimeStampType::class,
        'timestamptz' => TimeStampTzType::class,
        'datetimetz' => DateTimeTzType::class,
        'dateinterval' => IntervalType::class,
        'interval' => IntervalType::class,

        // List types
        'enum' => EnumType::class,
        'set' => SetType::class,
        'simple_array' => ArrayType::class,
        'array' => ArrayType::class,
        'json' => JsonType::class,
        'jsonb' => JsonbType::class,
        'json_array' => JsonType::class,

        // Binary types
        'bit' => BitType::class,
        'bit varying' => BitVaryingType::class,
        'binary' => BinaryType::class,
        'varbinary' => VarBinaryType::class,
        'tinyblob' => BlobType::class,
        'blob' => BlobType::class,
        'mediumblob' => BlobType::class,
        'longblob' => BlobType::class,
        'bytea' => ByteaType::class,

        // Network types
        'cidr' => CidrType::class,
        'inet' => InetType::class,
        'macaddr' => MacAddrType::class,
        'txid_snapshot' => TxidSnapshotType::class,

        // Geometry types
        'geometry' => GeometryType::class,
        'point' => PointType::class,
        'linestring' => LineStringType::class,
        'polygon' => PolygonType::class,
        'multipoint' => MultiPointType::class,
        'multilinestring' => MultiLineStringType::class,
        'multipolygon' => MultiPolygonType::class,
        'geometrycollection' => GeometryCollectionType::class,

        // Object types
        'object' => ObjectType::class,
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
