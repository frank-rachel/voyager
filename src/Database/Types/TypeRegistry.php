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
        // This should return an associative array mapping type names to their class names
        // Example mapping:
        return [
            'integer' => IntegerType::class,
            'varchar' => VarCharType::class,
            'numeric' => NumericType::class,
            // Add more mappings as needed
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
