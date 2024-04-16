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

    // Add more methods as needed based on Doctrine's Column API
}
