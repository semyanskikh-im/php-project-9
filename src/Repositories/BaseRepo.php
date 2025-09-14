<?php

namespace Hexlet\Code\Repositories;

use PDO;

abstract class BaseRepo
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllFromTable(string $tableName): array
    {
        try {
            $items = [];
            $sql = "SELECT * FROM {$tableName} ORDER BY created_at DESC";
            $stmt = $this->pdo->query($sql);

            while ($row = $stmt->fetch()) {
                $items[] = $this->makeEntityFromRow($row);
            }

            return $items;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    abstract public function makeEntityFromRow(array $row);
}
