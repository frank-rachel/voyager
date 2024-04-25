<?php

namespace TCG\Voyager\Database\Schema;

use TCG\Voyager\Database\Types\Type;
use TCG\Voyager\Database\Types\TypeRegistry;

class Column
{
    public $name;
    public $oldName;
    public $type;
    public $options;
    public $tableName;

    public function __construct($name, $type, array $options = [], $tableName = null)
    {
        $this->name = $name;
        $this->oldName = $name;
        $this->tableName = $tableName;

        // Resolving type instance if needed
        if (!($type instanceof Type)) {
            $typeName = is_array($type) && isset($type['name']) ? trim($type['name']) : trim($type);
            $this->type = Type::getType($typeName);
        } else {
            $this->type = $type;
        }

        $this->options = array_merge([
            'nullable' => true,
            'default' => null,
            'length' => null,
            'precision' => null,
            'scale' => null,
            'unsigned' => false,
            'fixed' => false,
            'notnull' => true
        ], $options);

        $this->options['notnull'] = !$this->options['nullable'];
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            // 'type' => $this->type->getName(),  // Assuming Type has a getName() method.
            'type' => Type::toArray($this->type),  // Assuming Type has a getName() method.
            'oldName' => $this->oldName,
            'null' => $this->options['nullable'] ? 'YES' : 'NO',
            'extra' => $this->getExtra(),  // Ensure this method is defined
            'notnull' => $this->options['notnull'],
            'fixed' => $this->options['fixed'],
            'unsigned' => $this->options['unsigned'],
            'default' => $this->options['default'],
            'length' => $this->options['length'],
            'precision' => $this->options['precision'],
            'scale' => $this->options['scale'],
            'composite' => false
        ];
    }
	
    public static function make(array $column, string $tableName = null)
    {
        $name = $column['name'];
        $type = $column['type'];
        $options = $column['options'] ?? [];
        return new self($name, $type, $options, $tableName);
    }
	
    public static function makeMany(array $columns, string $tableName = null)
    {
        return array_map(function ($column) use ($tableName) {
            return self::make($column, $tableName);
        }, $columns);
    }

    public function getName()
    {
        return $this->name;
    }
    public function getType()
    {
        // return $this->type;
        return $this->type->getName();
    }

    protected function getExtra()
    {
        $extra = '';
        if (isset($this->options['autoincrement']) && $this->options['autoincrement']) {
            $extra = 'auto_increment';
        }
        return $extra;
    }

    public function getUnsigned(): bool
    {
        return $this->options['unsigned'];
    }

    public function getFixed(): bool
    {
        return $this->options['fixed'];
    }

    public function getNotnull(): bool
    {
        return $this->options['notnull'];
    }
}
