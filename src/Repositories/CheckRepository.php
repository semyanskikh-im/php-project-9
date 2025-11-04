<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Entities\UrlCheck;

class CheckRepository extends BaseRepository
{
    public function create(array $data = []): UrlCheck
    {
        $urlCheck = new UrlCheck($data['url_id']);

        $urlCheck->setStatusCode($data['status_code']);
        $urlCheck->setH1($data['h1'] ?? null);
        $urlCheck->setTitle($data['title'] ?? null);
        $urlCheck->setDescription($data['description'] ?? null);

        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $urlCheck->getUrlId(),
            $urlCheck->getStatusCode(),
            $urlCheck->getH1(),
            $urlCheck->getTitle(),
            $urlCheck->getDescription(),
            $urlCheck->getCreatedAt()
        ]);
        $id = (int) $this->pdo->lastInsertId();
        $urlCheck->setId($id);

        return $urlCheck;
    }

    public function getAll(): array
    {
        return $this->getAllFromTable('url_checks');
    }

    public function makeEntityFromRow(array $row): UrlCheck
    {
        $urlCheck = UrlCheck::fromArray([
            $row['url_id'],
            $row['status_code'],
            $row['h1'],
            $row['title'],
            $row['description'],
            $row['created_at']
        ]);
        $urlCheck->setId($row['id']);

        return $urlCheck;
    }

    public function getAllForUrlId(int $urlId): array
    {
        $checks = [];
        $sql = "SELECT * FROM url_checks WHERE url_id = ?";
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
        $sql = "SELECT status_code, created_at FROM url_checks WHERE url_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }
}
