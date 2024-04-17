<?php
namespace TCG\Voyager\Database\Types;

abstract class Type
{
    public const NAME = 'UNDEFINED_TYPE_NAME';

    // Define the properties each type class should have
    public const DBTYPE = 'default_db_type';
    public $name;
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

	// In your Type class
	public function getSimpleOutput() {
		return $this->getName(); // Just return the type name as a string
	}


    // Getter for category
    public function getCategory()
    {
        return $this->category;
    }

    // Each type must implement its own SQL declaration method
    // abstract public function getSQLDeclaration(array $field);
    public function getSQLDeclaration(array $field)
    {
        $length = $field['length'] ?? 2000; // Default length
        return DBTYPE."($length)";
    }

    // Convert the type instance to an array format for easy handling
	public function toArray() {
		return [
			'name' => $this->getName(),
			'category' => $this->getCategory(),
			'default' => $this->getDefaultSettings()
		];
	}

	protected function getDefaultSettings() {
		// Return default settings based on the type. For example:
		if ($this->category === 'Numbers') {
			return ['type' => 'number', 'step' => 'any'];
		} else if ($this->category === 'Date and Time') {
			return ['type' => 'date'];
		}
		// Add other conditions as necessary
		return null;
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
	
}

