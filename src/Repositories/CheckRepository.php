<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Entities\UrlCheck;

class CheckRepository extends BaseRepository
{
    public function create(array $data = []): UrlCheck
    {
        $check = new UrlCheck($data['url_id']);

        $check->setStatusCode($data['status_code']);
        $check->setH1($data['h1'] ?? null);
        $check->setTitle($data['title'] ?? null);
        $check->setDescription($data['description'] ?? null);

        $sql = "INSERT INTO checks (url_id, status_code, h1, title, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $check->getUrlId(),
            $check->getStatusCode(),
            $check->getH1(),
            $check->getTitle(),
            $check->getDescription(),
            $check->getCreatedAt()
        ]);
        $id = (int) $this->pdo->lastInsertId();
        $check->setId($id);

        return $check;
    }

    public function getAll(): array
    {
        return $this->getAllFromTable('checks');
    }

    public function makeEntityFromRow(array $row): UrlCheck
    {
        $check = UrlCheck::fromArray([
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

    public function getLastCheckForUrl(int $urlId): ?array
    {
        $sql = "SELECT status_code, created_at FROM checks WHERE url_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}
