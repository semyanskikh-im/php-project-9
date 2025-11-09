<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Entities\UrlCheck;
use Carbon\Carbon;

class CheckRepository extends BaseRepository
{
    public function create(array $data = []): UrlCheck
    {
        $sql = "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                VALUES (?, ?, ?, ?, ?, ?);";
        $stmt = $this->pdo->prepare($sql);

        $createdAt = Carbon::now()->toDateTimeString();

        $stmt->execute([
            $data['url_id'],
            $data['status_code'],
            $data['h1'],
            $data['title'],
            $data['description'],
            $createdAt
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $urlCheck = new UrlCheck(
            $data['url_id'],
            $data['status_code'],
            $data['h1'],
            $data['title'],
            $data['description']
        );
        $urlCheck->setId($id);
        $urlCheck->setCreatedAt($createdAt);

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
        $sql = "SELECT * FROM url_checks WHERE url_id = ? ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlId]);

        while ($row = $stmt->fetch()) {
            $check = $this->makeEntityFromRow($row);
            $checks[] = $check;
        }
        return $checks;
    }

    public function findLastChecks(): array
    {
        $sql = "SELECT DISTINCT ON (url_id)
                url_id, status_code, created_at 
                FROM url_checks 
                ORDER BY url_id, created_at DESC";

        $stmt = $this->pdo->query($sql);

        $lastChecks = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $lastChecks[$row['url_id']] = [
                'status_code' => $row['status_code'],
                'created_at' => $row['created_at']
            ];
        }

        return $lastChecks;
    }
}
