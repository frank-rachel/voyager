<?php
namespace TCG\Voyager\Database\Types;

abstract class Type
{
    public const NAME = 'UNDEFINED_TYPE_NAME';

    // Define the properties each type class should have
    public const DBTYPE = 'default_db_type';
    protected $name;
    protected $category = 'Other';  // Default category

    public function __construct()
    {
        $this->name = static::NAME;
    }

    // Getter for name
    public function getName()
    {
        return $this->name;
    }

    // Getter for category
    public function getCategory()
    {
        return $this->category;
    }

    // Each type must implement its own SQL declaration method
    abstract public function getSQLDeclaration(array $field);

    // Convert the type instance to an array format for easy handling
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'category' => $this->getCategory()
        ];
    }

    // Method to initialize and return the type instance
    public static function getType($typeName)
    {
        $className = __NAMESPACE__ . '\\Postgresql\\' . $typeName;
        if (class_exists($className)) {
            return new $className();
        }
        throw new \Exception("Type $typeName does not exist.");
    }

    // Static method to register all types found in the specified directory
    public static function registerTypesFromDirectory($directory)
    {
        $types = [];
        foreach (glob($directory . '*.php') as $file) {
            $baseName = basename($file, '.php');
            $className = __NAMESPACE__ . '\\Postgresql\\' . $baseName;
            if (class_exists($className)) {
                $typeInstance = new $className();
                $types[$typeInstance->getName()] = $typeInstance->toArray();
            }
        }
        return $types;
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

