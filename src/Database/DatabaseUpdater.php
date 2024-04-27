<?php

namespace TCG\Voyager\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseUpdater
{
    protected $tableArr;
    protected $tableName;

    public function __construct(array $tableArr)
    {
        $this->tableArr = $tableArr;
        $this->tableName = $tableArr['oldName'];
    }

    public static function update(array $tableArr)
    {
        $updater = new self($tableArr);
        $updater->updateTable();
    }

    public function updateTable()
    {
        $this->checkTableExists();
        $this->handleTableRenaming();
        $this->processColumns();
        $this->processIndexes();
    }

    protected function checkTableExists()
    {
        if (!Schema::hasTable($this->tableName)) {
            throw new \Exception("Table '{$this->tableName}' does not exist.");
        }
    }

    protected function handleTableRenaming()
    {
        if (isset($this->tableArr['newName']) && $this->tableName !== $this->tableArr['newName']) {
            Schema::rename($this->tableName, $this->tableArr['newName']);
            $this->tableName = $this->tableArr['newName'];
        }
    }

    protected function processColumns()
    {
        Schema::table($this->tableName, function ($table) {
            foreach ($this->tableArr['columns'] as $column) {
                if ($column['oldName'] !== $column['name']) {
                    $table->renameColumn($column['oldName'], $column['name']);
                }
                // Example: Change column type or set a default value
                // Adjust according to actual requirements
                $table->string($column['name'])->default($column['default'])->change();
            }
        });
    }

    protected function processIndexes()
    {
        // Handle adding, removing, or modifying indexes
    }
}
