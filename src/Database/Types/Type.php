<?php

namespace TCG\Voyager\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform as DoctrineAbstractPlatform;
use Doctrine\DBAL\Types\Type as DoctrineType;
use TCG\Voyager\Database\Platforms\Platform;
use TCG\Voyager\Database\Schema\SchemaManager;

abstract class Type
{
	
    // Abstract method to get SQL declaration
    abstract public function getSQLDeclaration(array $field, AbstractPlatform $platform): string;

    // Default conversion to database value
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // Default implementation, override in child classes if needed
        return $value;
    }

    // Default conversion to PHP value
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Default implementation, override in child classes if needed
        return $value;
    }
}
/*	
    protected static $customTypesRegistered = false;
    protected static $platformTypeMapping = [];
    protected static $allTypes = [];
    protected static $platformTypes = [];
    protected static $customTypeOptions = [];
    protected static $typeCategories = [];

    public const NAME = 'UNDEFINED_TYPE_NAME';
    public const NOT_SUPPORTED = 'notSupported';
    public const NOT_SUPPORT_INDEX = 'notSupportIndex';

    // todo: make sure this is not overwrting DoctrineType properties

    // Note: length, precision and scale need default values manually

    public function getName()
    {
        return static::NAME;
    }

    public static function toArray(DoctrineType $type)
    {
        $customTypeOptions = $type->customOptions ?? [];

		$reflection = new \ReflectionClass($type);
		$className = $reflection->getShortName(); // If you have an object instance

		// For the sake of this example, we directly manipulate the string
		// Strip the namespace to get the basename
		$baseName = basename(str_replace('\\', '/', $className));

		// Remove 'Type' suffix
		$baseNameWithoutType = preg_replace('/Type$/', '', $baseName);

		// echo $baseNameWithoutType; // Outputs: Integer

        return array_merge([
            // 'name' => $type->getName(),
            'name' => $baseNameWithoutType,
        ], $customTypeOptions);
    }

    public static function getPlatformTypes()
    {
        if (static::$platformTypes) {
            return static::$platformTypes;
        }

        if (!static::$customTypesRegistered) {
            static::registerCustomPlatformTypes();
        }

        $platform = SchemaManager::getDatabasePlatform();
        $platformname = SchemaManager::getName();

        static::$platformTypes = Platform::getPlatformTypes(
            $platformname,
            static::getPlatformTypeMapping($platform)
        );

        static::$platformTypes = static::$platformTypes->map(function ($type) {
            return static::toArray(static::getType($type));
        })->groupBy('category');

        return static::$platformTypes;
    }

    public static function getPlatformTypeMapping(DoctrineAbstractPlatform $platform)
    {
        if (static::$platformTypeMapping) {
            return static::$platformTypeMapping;
        }

        static::$platformTypeMapping = collect(
            get_protected_property($platform, 'doctrineTypeMapping')
        );

        return static::$platformTypeMapping;
    }

    public static function registerCustomPlatformTypes($force = false)
    {
        if (static::$customTypesRegistered && !$force) {
            return;
        }

        $platform = SchemaManager::getDatabasePlatform();
        // $platformName = ucfirst($platform->getName());
        // $platformName = ucfirst(preg_replace('/Platform$/', '', $platform));
		// $reflection = new \ReflectionClass($platform);
		// $shortName = $reflection->getShortName(); // Gets the short class name
		// $platformName = ucfirst(strtolower(preg_replace('/Platform$/', '', $shortName)));
		$platformName = SchemaManager::getName();

        $customTypes = array_merge(
            static::getPlatformCustomTypes('Common'),
            static::getPlatformCustomTypes($platformName)
        );

        foreach ($customTypes as $type) {
            $name = $type::NAME;

            if (static::hasType($name)) {
                static::overrideType($name, $type);
            } else {
                static::addType($name, $type);
            }

            $dbType = defined("{$type}::DBTYPE") ? $type::DBTYPE : $name;

            $platform->registerDoctrineTypeMapping($dbType, $name);
        }

        static::addCustomTypeOptions($platformName);

        static::$customTypesRegistered = true;
    }

    protected static function addCustomTypeOptions($platformName)
    {
        static::registerCommonCustomTypeOptions();

        Platform::registerPlatformCustomTypeOptions($platformName);

        // Add the custom options to the types
        foreach (static::$customTypeOptions as $option) {
            foreach ($option['types'] as $type) {
                if (static::hasType($type)) {
                    static::getType($type)->customOptions[$option['name']] = $option['value'];
                }
            }
        }
    }

    protected static function getPlatformCustomTypes($platformName)
    {
        $typesPath = __DIR__.DIRECTORY_SEPARATOR.$platformName.DIRECTORY_SEPARATOR;
        $namespace = __NAMESPACE__.'\\'.$platformName.'\\';
        $types = [];

		// Sanitize and trim to avoid null bytes and unwanted characters
		$typesPath = trim(str_replace("\0", "", $typesPath));

		// Append the pattern for glob
		$pattern = $typesPath . '*.php';

		// Use glob() to find matching files
		foreach (glob($pattern) as $classFile) {
        // foreach (glob($typesPath.'*.php') as $classFile) {
            $types[] = $namespace.str_replace(
                '.php',
                '',
                str_replace($typesPath, '', $classFile)
            );
        }

        return $types;
    }

    public static function registerCustomOption($name, $value, $types)
    {
        if (is_string($types)) {
            $types = trim($types);

            if ($types == '*') {
                $types = static::getAllTypes()->toArray();
            } elseif (strpos($types, '*') !== false) {
                $searchType = str_replace('*', '', $types);
                $types = static::getAllTypes()->filter(function ($type) use ($searchType) {
                    return strpos($type, $searchType) !== false;
                })->toArray();
            } else {
                $types = [$types];
            }
        }

        static::$customTypeOptions[] = [
            'name'  => $name,
            'value' => $value,
            'types' => $types,
        ];
    }

    protected static function registerCommonCustomTypeOptions()
    {
        static::registerTypeCategories();
        static::registerTypeDefaultOptions();
    }

    protected static function registerTypeDefaultOptions()
    {
        $types = static::getTypeCategories();

        // Numbers
        static::registerCustomOption('default', [
            'type' => 'number',
            'step' => 'any',
        ], $types['numbers']);

        // Date and Time
        static::registerCustomOption('default', [
            'type' => 'date',
        ], 'date');
        static::registerCustomOption('default', [
            'type' => 'time',
            'step' => '1',
        ], 'time');
        static::registerCustomOption('default', [
            'type' => 'number',
            'min'  => '0',
        ], 'year');
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
        // static::registerCustomOption('category', 'Special', $types['special']);
    }

    public static function getAllTypes()
    {
        if (static::$allTypes) {
            return static::$allTypes;
        }

        static::$allTypes = collect(static::getTypeCategories())->flatten();

        return static::$allTypes;
    }

    public static function getTypesMap() {
		return [
            'bigint'           => Types::BIGINT,
            'bigserial'        => Types::BIGINT,
            'bool'             => Types::BOOLEAN,
            'boolean'          => Types::BOOLEAN,
            'bpchar'           => Types::STRING,
            'bytea'            => Types::BLOB,
            'char'             => Types::STRING,
            'date'             => Types::DATE_MUTABLE,
            'datetime'         => Types::DATETIME_MUTABLE,
            'decimal'          => Types::DECIMAL,
            'double'           => Types::FLOAT,
            'double precision' => Types::FLOAT,
            'float'            => Types::FLOAT,
            'float4'           => Types::FLOAT,
            'float8'           => Types::FLOAT,
            'inet'             => Types::STRING,
            'int'              => Types::INTEGER,
            'int2'             => Types::SMALLINT,
            'int4'             => Types::INTEGER,
            'int8'             => Types::BIGINT,
            'integer'          => Types::INTEGER,
            'interval'         => Types::STRING,
            'json'             => Types::JSON,
            'jsonb'            => Types::JSON,
            'money'            => Types::DECIMAL,
            'numeric'          => Types::DECIMAL,
            'serial'           => Types::INTEGER,
            'serial4'          => Types::INTEGER,
            'serial8'          => Types::BIGINT,
            'real'             => Types::FLOAT,
            'smallint'         => Types::SMALLINT,
            'text'             => Types::TEXT,
            'time'             => Types::TIME_MUTABLE,
            'timestamp'        => Types::DATETIME_MUTABLE,
            'timestamptz'      => Types::DATETIMETZ_MUTABLE,
            'timetz'           => Types::TIME_MUTABLE,
            'tsvector'         => Types::TEXT,
            'uuid'             => Types::GUID,
            'varchar'          => Types::STRING,
            'year'             => Types::DATE_MUTABLE,
            '_varchar'         => Types::STRING,
        ];
	}

public static function translateToLaravelTypes($postgresType)
{
    // Mapping of PostgreSQL types to Laravel Schema Blueprint methods
    $mapping = [
        'bigint' => 'bigInteger',
        'bigserial' => 'bigIncrements',
        'boolean' => 'boolean',
        'bytea' => 'binary',
        'char' => 'char',
        'date' => 'date',
        'decimal' => 'decimal',
        'double precision' => 'double',
        'enum' => 'string', // Laravel doesn't have a native enum type; handle via custom migration or string
        'float' => 'float',
        'integer' => 'integer',
        'json' => 'json',
        'jsonb' => 'jsonb',
        'numeric' => 'decimal',
        'real' => 'float',
        'serial' => 'increments', // Typically used for auto-increment integers
        'smallint' => 'smallInteger',
        'text' => 'text',
        'timestamp' => 'timestamp',
        'timestamptz' => 'timestampTz',
        'time' => 'time',
        'timetz' => 'timeTz',
        'uuid' => 'uuid',
        'varchar' => 'string',
        // Add more mappings as needed
    ];

    // Return the Laravel Schema type if available, otherwise null
    return $mapping[$postgresType] ?? null;
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
	// */
	/*
public static function getTypeCategories()
{
    if (static::$typeCategories) {
        return static::$typeCategories;
    }

    $numbers = [
        'boolean',
        'tinyInteger', // Assuming this maps to tinyint
        'smallInteger', // Assuming this maps to smallint
        'mediumInteger', // Assuming this maps to mediumint
        'integer', // Assuming this maps to int
        'bigInteger', // Assuming this maps to bigint
        'decimal',
        'float', // Can map to real or double precision depending on context
        'double', // Typically maps to double precision
        'unsignedBigInteger',
        'unsignedInteger',
        'unsignedMediumInteger',
        'unsignedSmallInteger',
        'unsignedTinyInteger',
    ];

    $strings = [
        'char',
        'string', // Assuming this maps to varchar or char
        'text',
        'mediumText',
        'longText',
        'tinyText',
        'enum',
        'set', // Note: set is not directly supported in PostgreSQL
        'json',
        'jsonb',
        'uuid',
        'ipAddress', // Assuming this maps to inet or cidr
        'macAddress',
    ];

    $datetime = [
        'dateTime',
        'dateTimeTz',
        'date',
        'time',
        'timeTz',
        'timestamp',
        'timestampTz',
        'timestamps', // Laravel specific, creates created_at and updated_at
        'timestampsTz', // Laravel specific, for timezone aware timestamps
        'year', // Note: year is not a native PostgreSQL type
    ];

    $binary = [
        'binary', // Assuming this maps to bytea or blob types
    ];

    $geometry = [
        'geometry',
        'geography', // Assuming these map to spatial types if using PostGIS
    ];

    // Laravel specific types or special cases
    $special = [
        'increments',
        'mediumIncrements',
        'smallIncrements',
        'tinyIncrements',
        'bigIncrements',
        'foreignId',
        'foreignIdFor',
        'foreignUlid',
        'foreignUuid',
        'morphs',
        'nullableMorphs',
        'nullableTimestamps',
        'nullableUlidMorphs',
        'nullableUuidMorphs',
        'ulid',
        'rememberToken', // Typically a specific use case of string type
        'softDeletes', // Laravel specific, typically a datetime
        'softDeletesTz', // Laravel specific, timezone aware datetime
        'ulidMorphs',
        'uuidMorphs',
    ];

    static::$typeCategories = [
        'numbers'  => $numbers,
        'strings'  => $strings,
        'datetime' => $datetime,
        'binary'   => $binary,
        'geometry' => $geometry,
        'special'  => $special, // Added for Laravel specific or unique cases
    ];

    return static::$typeCategories;
}
	// */
	
}
