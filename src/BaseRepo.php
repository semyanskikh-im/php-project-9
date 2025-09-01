<?php

namespace Hexlet\Code;

use PDO;

abstract class BaseRepo
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllFromTable(string $tableName, string $orderBy = 'created_at DESC'): array
    {
        $items = [];
        $sql = "SELECT * FROM {$tableName} ORDER BY {$orderBy}";
        $stmt = $this->pdo->query($sql);

        while ($row = $stmt->fetch()) {
            $items[] = $this->makeEntityFromRow($row);
        }

        return $items;
    }

    abstract public function makeEntityFromRow(array $row);
}
