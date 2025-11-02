<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Entities\Url;
use Hexlet\Code\Repositories\CheckRepository;


class UrlRepository extends BaseRepository
{
    public function getAll(): array
    {
        return $this->getAllFromTable('urls');
    }

    public function create(array $data = []): Url
    {
        $urlName = $data['name'];
        $url = new Url($urlName);
        $createdAt = $url->getCreatedAt();
        $sql = "INSERT INTO urls (name, created_at) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlName, $createdAt]);
        $id = (int) $this->pdo->lastInsertId();
        $url->setId($id);

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

        public function findAllWithLastCheck(CheckRepository $checkRepo): array
    {
        $urls = $this->getAll();

        $result = [];
        foreach ($urls as $url) {
            $lastCheck = $checkRepo->getLastCheckForUrl($url->getId());

            $urlData = [
                'id' => $url->getId(),
                'name' => $url->getUrlName(),
                'created_at' => $url->getCreatedAt(),
                'last_check' => $lastCheck
            ];

            $result[] = $urlData;
        }

        return $result;
    }
}
