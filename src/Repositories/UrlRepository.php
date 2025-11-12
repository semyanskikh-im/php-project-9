<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Entities\Url;
use Hexlet\Code\Repositories\UrlCheckRepository;
use Carbon\Carbon;

class UrlRepository extends BaseRepository
{
    public function getAll(): array //возвращается массив объектов класса Url
    {
        return $this->getAllFromTable('urls');
    }

    public function create(array $data = []): Url
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);

        $urlName = $data['name'];
        $createdAt = Carbon::now()->toDateTimeString();

        $stmt->execute([$urlName, $createdAt]);

        $id = (int) $this->pdo->lastInsertId();
        $url = new Url($urlName);
        $url->setId($id);
        $url->setCreatedAt($createdAt);

        return $url;
    }

    public function findById(int $id): ?Url
    {
        $sql = "SELECT * FROM urls WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            return $this->makeEntityFromRow($row);
        }

        return null;
    }

    public function findByName(string $urlName): ?Url
    {
        $sql = "SELECT * FROM urls WHERE name = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlName]);
        if ($row = $stmt->fetch()) {
            return $this->makeEntityFromRow($row);
        }

        return null;
    }

    public function makeEntityFromRow(array $row): Url
    {
        $url = Url::fromArray([$row['name'], $row['created_at']]);
        $url->setId($row['id']);

        return $url;
    }

    public function findAllWithLastCheck(UrlCheckRepository $checkRepository): array
    {
        $urls = $this->getAll();

        $lastChecks = $checkRepository->findLastChecks();

        $result = [];
        foreach ($urls as $url) {
            $urlId = $url->getId();

            $result[] = [
                'id' => $urlId,
                'name' => $url->getUrlName(),
                'created_at' => $lastChecks[$urlId]['created_at'] ?? null,
                'status_code' => $lastChecks[$urlId]['status_code'] ?? null
            ];
        }

        return $result;
    }
}
