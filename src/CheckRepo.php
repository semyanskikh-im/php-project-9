<?php

namespace Hexlet\Code;

use Hexlet\Code\Check;

class CheckRepo extends BaseRepo

{
    public function create(int $urlId, $statusCode = null, $h1 = null, $title = null, $description = null): Check
    {
        $check = new Check();
        $check->setUrlId($urlId);
        $check->setStatusCode($statusCode);
        $check->setH1($h1);
        $check->setTitle($title);
        $check->setDescription($description);
        $createdAt = $check->getCreatedAt();
        $sql = "INSERT INTO checks (url_id, status_code, h1, title, description, created_at) VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlId, $statusCode, $h1, $title, $description, $createdAt]);
        $id = (int) $this->pdo->lastInsertId();
        $check->setId($id);

        return $check;
    }

    public function getAll(): array
    {
        return $this->getAllFromTable('checks');
    }

    public function makeEntityFromRow(array $row): Check
    {
        $check = Check::fromArray([
            $row['url_id'],
            $row['status_code'],
            $row['h1'],
            $row['title'],
            $row['description'],
            $row['created_at']
        ]);
        $check->setId($row['id']);

        return $check;
    }

    public function getAllForUrlId(int $urlId): array
    {
        $checks = [];
        $sql = "SELECT * FROM checks WHERE url_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlId]);

        while ($row = $stmt->fetch()) {
            $check = $this->makeEntityFromRow($row);
            $checks[] = $check;
        }
        return $checks;
    }
}
