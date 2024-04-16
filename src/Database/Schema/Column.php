<?php
namespace TCG\Voyager\Database\Schema;

class Column
{
    public $name;
    public $type;
    public $options;

    public function __construct($name, $type, $options = [])
    {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    // Basic getters
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

    // Emulate Doctrine's method if used elsewhere in your application
    public function getDefault()
    {
        return $this->options['default'] ?? null;
    }

    public function isNotNull()
    {
        return $this->options['notnull'] ?? false;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'type' => $this->type, // If type is an object, ensure it has a method to serialize itself
            'options' => $this->options,
            'null' => $this->options['notnull'] ?? true ? 'NO' : 'YES', // Example conversion
            // 'extra' => $this->getExtra(),
            'composite' => false // Example default
        ];
    }
	
    protected static function getExtra(Column $column)
    {
        $extra = '';

        $extra .= $column->getAutoincrement() ? 'auto_increment' : '';
        // todo: Add Extra stuff like mysql 'onUpdate' etc...

        return $extra;
    }	

    // Add more methods as needed based on Doctrine's Column API
}
