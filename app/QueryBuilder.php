<?php

namespace App;

class QueryBuilder
{
    private $pdo;
    private $query;

    public function __construct($tablename)
    {
        $config = require __DIR__ . '/../config/loader.php';

        $this->pdo = new \PDO("sqlite:{$config['DB_PATH']}");
        $this->query = "SELECT * FROM {$tablename}";

        return $this;
    }

    public static function fromTable($tablename)
    {
        return new self($tablename);
    }

    public function where($field, $value, $op = '=')
    {
        if (is_string($value)) {
            $value = "'{$value}'";
        }

        $this->query .= " WHERE {$field} ${op} {$value}";

        return $this;
    }

    public function first($n = '1')
    {
        $query = "{$this->query} LIMIT {$n}";
        $method = $n > 1 ? 'fetchAll' : 'fetch';

        return $this->run($query, $method);
    }

    public function all()
    {
        return $this->run($this->query, 'fetchAll');
    }

    private function run($query, $method)
    {
        $stmt = $this->pdo->prepare($query);

        return $stmt->execute()
            ? $stmt->$method(\PDO::FETCH_ASSOC)
            : [];
    }
}
