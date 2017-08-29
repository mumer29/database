<?php

namespace Odan\Database;

use PDOStatement;

/**
 * Class InsertQuery
 *
 * https://dev.mysql.com/doc/refman/5.7/en/insert.html
 */
class InsertQuery
{
    /**
     * Connection
     *
     * @var Connection
     */
    protected $pdo;

    protected $table;

    protected $values;

    protected $duplicateValues;

    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    public function into($table)
    {
        $this->table = $table;
        return $this;
    }

    public function values(array $values)
    {
        $this->values = $values;
        return $this;
    }

    // @todo
    // [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
    // [ON DUPLICATE KEY UPDATE assignment_list]

    public function onDuplicateKeyUpdate($values)
    {
        $this->duplicateValues = $values;
        return $this;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        return $this->getStatement()->execute();
    }

    /**
     * @return PDOStatement
     */
    public function getStatement()
    {
        return $this->pdo->prepare($this->getSql());
    }

    /**
     * @return string SQL string
     */
    public function getSql()
    {
        $table = $this->pdo->quoteName($this->table);

        $result = '';

        if (array_key_exists(0, $this->values)) {
            // multiple rows
            // INSERT INTO tbl_name (a,b,c) VALUES(1,2,3),(4,5,6),(7,8,9)
            $result = sprintf("INSERT INTO %s (%s) VALUES", $table, $this->getInsertQuoteFields($this->values[0]));
            foreach ($this->values as $key => $row) {
                $result .= sprintf("%s(%s)", ($key > 0) ? ',' : '', $this->getInsertBulkValues($row));
            }
        } else {
            // single row
            $values = $this->getInsertValues($this->values);
            $result = sprintf("INSERT INTO %s SET %s", $table, $values);
        }

        if ($this->duplicateValues) {
            $values = $this->getInsertValues($this->duplicateValues);
            $result .= sprintf(' ON DUPLICATE KEY UPDATE %s', $values);
        }
        $result .= ';';

        return $result;
    }

    protected function getInsertValues($row): string
    {
        $values = [];
        foreach ($row as $key => $value) {
            $values[] = $this->pdo->quoteName($key) . '=' . $this->pdo->quoteValue($value);
        }
        return implode(', ', $values);
    }

    protected function getInsertBulkValues($row): string
    {
        $values = [];
        foreach ($row as $key => $value) {
            $values[] =  $this->pdo->quoteValue($value);
        }
        return implode(',', $values);
    }

    protected function getInsertQuoteFields(array $row): string
    {
        $fields = [];
        foreach (array_keys($row) as $field) {
            $fields[] =  $this->pdo->quoteName($field);
        }
        return implode(', ', $fields);
    }
}
