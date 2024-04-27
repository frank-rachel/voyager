<?php
namespace TCG\Voyager\Database;

use PDO;

class DatabaseUpdater
{
    protected $pdo;
    protected $tableArr;
    protected $tableName;

    public function __construct(array $tableArr, PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->tableArr = $tableArr;
        $this->tableName = $tableArr['oldName'];
    }

    public static function update($tableArr, PDO $pdo)
    {
        $updater = new self($tableArr, $pdo);
        $updater->updateTable();
    }

    public function updateTable()
    {
        $this->checkTableExists();
        $this->handleTableRenaming();
        $this->processColumns();
        $this->processIndexes();
        // Additional schema update methods can be added here
    }

    protected function checkTableExists()
    {
        if (!$this->tableExists($this->tableName)) {
            throw new \Exception("Table '{$this->tableName}' does not exist.");
        }
    }

    protected function handleTableRenaming()
    {
        if (isset($this->tableArr['newName']) && $this->tableName !== $this->tableArr['newName']) {
            $this->renameTable($this->tableName, $this->tableArr['newName']);
            $this->tableName = $this->tableArr['newName'];
        }
    }

    protected function processColumns()
    {
        foreach ($this->tableArr['columns'] as $column) {
            if ($column['oldName'] !== $column['name']) {
                $this->renameColumn($this->tableName, $column['oldName'], $column['name']);
            }
            $this->modifyColumn($column);  // Assuming modification includes type change, default value, etc.
        }
    }

    protected function processIndexes()
    {
        // Placeholder for index processing logic
        // This would typically handle adding, removing, or modifying indexes
    }

    protected function tableExists($tableName)
    {
        $stmt = $this->pdo->prepare("SELECT to_regclass(:table_name)");
        $stmt->execute(['table_name' => $tableName]);
        return $stmt->fetchColumn() !== null;
    }

    protected function renameTable($oldName, $newName)
    {
        $sql = "ALTER TABLE \"$oldName\" RENAME TO \"$newName\"";
        $this->pdo->exec($sql);
    }

    protected function renameColumn($tableName, $oldColumnName, $newColumnName)
    {
        $sql = "ALTER TABLE \"$tableName\" RENAME COLUMN \"$oldColumnName\" TO \"$newColumnName\"";
        $this->pdo->exec($sql);
    }

    protected function modifyColumn($column)
    {
        // Example SQL for modifying a column's type or setting a default
        $sql = "ALTER TABLE \"{$this->tableName}\" ALTER COLUMN \"{$column['name']}\" TYPE {$column['type']} USING \"{$column['name']}\"::{$column['type']}";
        if (isset($column['default'])) {
            $sql .= ", ALTER COLUMN \"{$column['name']}\" SET DEFAULT '{$column['default']}'";
        }
        $this->pdo->exec($sql);
    }
}
