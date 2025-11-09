<?php

namespace Hexlet\Code\Repositories;

use PDO;

abstract class BaseRepository
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function getAllFromTable(string $tableName): array // возвращается массив объектов класса Url
    {
        $items = [];
        $sql = "SELECT * FROM {$tableName} ORDER BY created_at ASC";
        $stmt = $this->pdo->query($sql);

        if ($stmt === false) {
            error_log("Query failed for table: {$tableName}");
            return [];
        }

        while ($row = $stmt->fetch()) {
            $items[] = $this->makeEntityFromRow($row);
        }

        return $items;
    }

    abstract public function makeEntityFromRow(array $row);
}
