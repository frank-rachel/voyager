<?php
namespace TCG\Voyager\Database\Schema;

use Illuminate\Support\Facades\DB;

class Index
{
    public const PRIMARY = 'PRIMARY';
    public const UNIQUE = 'UNIQUE';
    public const INDEX = 'INDEX';

    public $name;
    public $columns;
    public $isUnique;
    public $isPrimary;
    public $flags;
    public $options;

    public function __construct($name, array $columns, $type, $isPrimary = false, $isUnique = false, array $flags = [], array $options = [])
    {
        $this->name = $name;
        $this->columns = $columns;
        $this->isPrimary = $isPrimary;
        $this->isUnique = $isUnique || $isPrimary;
        $this->flags = $flags;
        $this->options = $options;
    }

    public static function createIndex($name, $table, array $columns, $type)
    {
        $columnsList = implode(',', $columns);
        switch ($type) {
            case self::PRIMARY:
                DB::statement("ALTER TABLE {$table} ADD PRIMARY KEY ({$columnsList});");
                break;
            case self::UNIQUE:
                DB::statement("CREATE UNIQUE INDEX {$name} ON {$table} ({$columnsList});");
                break;
            case self::INDEX:
                DB::statement("CREATE INDEX {$name} ON {$table} ({$columnsList});");
                break;
        }

        return new self($name, $columns, $type, $type === self::PRIMARY, $type === self::UNIQUE);
    }

    public function toArray()
    {
        return [
            'name'        => $this->name,
            'columns'     => $this->columns,
            'type'        => $this->isPrimary ? self::PRIMARY : ($this->isUnique ? self::UNIQUE : self::INDEX),
            'isPrimary'   => $this->isPrimary,
            'isUnique'    => $this->isUnique,
            'isComposite' => count($this->columns) > 1,
            'flags'       => $this->flags,
            'options'     => $this->options,
        ];
    }
}
