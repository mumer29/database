<?php

namespace Odan\Database;

use PDO;

class Connection extends PDO
{
    /**
     * @var Quoter
     */
    protected $quoter;

    /**
     * Get quoter.
     *
     * @return Quoter
     */
    public function getQuoter()
    {
        if (!$this->quoter) {
            $this->quoter = new Quoter($this);
        }
        return $this->quoter;
    }

    /**
     * Retrieving a list of column values
     *
     * sample:
     * $lists = $db->queryValues('SELECT id FROM table;', 'id');
     *
     * @param string $sql
     * @param string $key
     * @return array
     */
    public function queryValues(string $sql, string $key): array
    {
        $result = [];
        $statement = $this->query($sql);
        while ($row = $statement->fetch()) {
            $result[] = $row[$key];
        }
        return $result;
    }

    /**
     * Retrieve only the given column of the first result row
     *
     * @param string $sql
     * @param string $column
     * @param mixed $default
     * @return mixed|null
     */
    public function queryValue(string $sql, string $column, $default = null)
    {
        $result = $default;
        if ($row = $this->query($sql)->fetch()) {
            $result = $row[$column];
        }
        return $result;
    }

    /**
     * Map query result by column as new index
     *
     * <code>
     * $rows = $db->queryMapColumn('SELECT * FROM table;', 'id');
     * </code>
     *
     * @param string $sql
     * @param string $key Column name to map as index
     * @return array
     */
    public function queryMapColumn(string $sql, string $key): array
    {
        $result = [];
        $statement = $this->query($sql);
        while ($row = $statement->fetch()) {
            $result[$row[$key]] = $row;
        }
        return $result;
    }
}
