<?php

namespace TCG\Voyager\Database\Schema;

use TCG\Voyager\Database\Types\Type;

class Column
{
    public $name;
    public $type;
    public $options;
    public $tableName;

    public function __construct($name, $type, array $options = [], $tableName = null)
    {
        $this->name = $name;
        $this->type = Type::getType($type);  // Assuming getType returns an instance of Type properly initialized
        $this->options = $options;
        $this->tableName = $tableName;

        // Set defaults from options or use default values
        $this->options['nullable'] = $options['nullable'] ?? true;
        $this->options['default'] = $options['default'] ?? null;
        $this->options['length'] = $options['length'] ?? null;
        $this->options['precision'] = $options['precision'] ?? null;
        $this->options['scale'] = $options['scale'] ?? null;
        $this->options['unsigned'] = $options['unsigned'] ?? false;
        $this->options['fixed'] = $options['fixed'] ?? false;
        $this->options['notnull'] = $options['notnull'] ?? !$this->options['nullable'];
    }

    public static function make(array $column, string $tableName = null)
    {
        $name = Identifier::validate($column['name'], 'Column');
        $type = Type::getType(trim($column['type']['name']));
        $type->tableName = $tableName;

        $options = array_diff_key($column, array_flip(['name', 'composite', 'oldName', 'null', 'extra', 'type', 'charset', 'collation']));

        return new self($name, $type, $options, $tableName);
    }

    public function toArray()
    {
        $typeArray = method_exists($this->type, 'toArray') ? $this->type->toArray() : (string)$this->type;

        return [
            'name' => $this->name,
            'type' => $typeArray,
            'oldName' => $this->name,
            'null' => $this->options['nullable'] ? 'YES' : 'NO',
            'default' => $this->options['default'],
            'length' => $this->options['length'],
            'precision' => $this->options['precision'],
            'scale' => $this->options['scale'],
            'unsigned' => $this->getUnsigned(),
            'fixed' => $this->getFixed(),
            'notnull' => $this->getNotnull(),
            'extra' => $this->getExtra(),
            'composite' => false
        ];
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

