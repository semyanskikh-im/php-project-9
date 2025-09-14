<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Check;

class CheckRepo extends BaseRepo
{
    public function create(int $urlId, int $statusCode, array $data = []): Check
    {
        $check = new Check();
        $check->setUrlId($urlId);
        $check->setStatusCode($statusCode);
        $check->setH1($data['h1'] ?? null);
        $check->setTitle($data['title'] ?? null);
        $check->setDescription($data['description'] ?? null);
        $createdAt = $check->getCreatedAt();
        $sql = "INSERT INTO checks (url_id, status_code, h1, title, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlId,
                        $statusCode,
                        $check->getH1(),
                        $check->getTitle(),
                        $check->getDescription(),
                        $createdAt]);
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
