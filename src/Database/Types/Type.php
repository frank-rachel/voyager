<?php

namespace TCG\Voyager\Database\Types;

use Illuminate\Support\Collection;
use Illuminate\Database\Schema\Blueprint;

abstract class Type
{
    protected static $customTypesRegistered = false;
    protected static $allTypes = [];
    protected static $typeCategories = [];

    public const NAME = 'UNDEFINED_TYPE_NAME';

    public function getName()
    {
        return static::NAME;
    }

    public static function toArray(Type $type)
    {
        return [
            'name' => $type->getName(),
        ];
    }

    public static function getAllTypes()
    {
        return static::$allTypes;
    }
	
	public static function getType($name)
	{
		foreach (static::$allTypes as $type) {
			if ($type::NAME === $name) {
				return new $type;
			}
		}
		throw new \Exception("Type not found: " . $name);
	}

	public static function registerCustomPlatformTypes($force = false)
	{
		if (static::$customTypesRegistered && !$force) {
			return;
		}

		$platformName = static::getPlatformName();

		static::$allTypes = static::getPlatformCustomTypes($platformName);

		foreach (static::$allTypes as $typeClass) {
			$typeInstance = new $typeClass();
			$name = $typeInstance->getName();
			Blueprint::macro($name, function ($column) use ($typeInstance) {
				// Implement the specifics of the type handling here
				// For example:
				return $this->addColumn($typeInstance::NAME, $column);
			});
		}

		static::$customTypesRegistered = true;
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
		print_r($types);
		exit;
        return $types;
    }

    protected static function getPlatformName()
    {
        return 'PostgreSQL';
    }

    public static function getTypeCategories()
    {
        if (!empty(static::$typeCategories)) {
            return static::$typeCategories;
        }

        static::initializeTypeCategories();
        return static::$typeCategories;
    }

    protected static function initializeTypeCategories()
    {
        static::$typeCategories = [
            'numbers' => [
                'boolean', 'integer', 'float', 'smallint', 'bigint', 'numeric'
            ],
            'strings' => [
                'char', 'varchar', 'text'
            ],
            'datetime' => [
                'date', 'timestamp', 'timestamptz', 'time', 'timetz', 'interval'
            ],
            'json' => [
                'json', 'jsonb'
            ],
            'arrays' => [
                'array', // specify more or custom array types as needed
            ],
            'network' => [
                'cidr', 'inet', 'macaddr'
            ],
            // Add other PostgreSQL-specific types as necessary
        ];
    }
}
