<?php
namespace TCG\Voyager\Database\Schema;

class Column
{
    protected $name;
    protected $type;
    protected $options;

    public function __construct($name, $type, $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    public static function make(array $column, string $tableName = null)
    {
        $name = Identifier::validate($column['name'], 'Column');
        $type = $column['type']; // Ensure this is a simple type name or an object handling type logic
        $options = array_diff_key($column, array_flip(['name', 'type', 'tableName']));

        if (!empty($tableName)) {
            $options['tableName'] = $tableName;
        }

        return new self($name, $type, $options);
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'type' => $this->type, // If type is an object, ensure it has a method to serialize itself
            'options' => $this->options,
            'null' => $this->options['notnull'] ?? true ? 'NO' : 'YES', // Example conversion
            'extra' => $this->getExtra(),
            'composite' => false // Example default
        ];
    }

    protected function getExtra()
    {
        // Handle any extra properties, such as auto_increment
        $extra = '';
        if (!empty($this->options['autoincrement']) && $this->options['autoincrement']) {
            $extra = 'auto_increment';
        }
        return $extra;
    }

    // Getter methods for name, type, options, etc.
    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
