<?php

namespace Hexlet\Code;

use PDO;
use Hexlet\Code\Url;

class UrlRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM urls ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);

        while ($row = $stmt->fetch()) {
            $url = new Url();
            $url->fromArray([$row['name'], $row['created_at']]);
            $url->setId($row['id']);
            $urls[] = $url;
        }

        return $urls;
    }

    public function create(string $urlName): Url
    {
        $url = new Url();
        $url->setUrlName($urlName);
        $createdAt = $url->getCreatedAt();
        $sql = "INSERT INTO urls (name, created_at) VALUES (:name, :created_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $urlName);
        $stmt->bindParam(':created_at', $createdAt);
        $stmt->execute();
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
            $url = Url::fromArray([$row['name'], $row['created_at']]);
            $url->setId($row['id']);
            return $url;
        }

        return null;
    }

    public function findByName(string $urlName): ?Url
    {
        $sql = "SELECT * FROM urls WHERE name = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$urlName]);
        if ($row = $stmt->fetch()) {
            $url = Url::fromArray([$row['name'], $row['created_at']]);
            $url->setId($row['id']);
            return $url;
        }

        return null;
    }

    // public function isUrlExists(string $urlName): bool
    // {
    //     $sql = "SELECT id FROM urls WHERE name = ?";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute([$urlName]);
    //     $row = $stmt->fetch();
    //     return $row['id'] ? true : false; 
    // }
}


//$statement = $pdo->prepare(' SELECT * FROM urls WHERE id = :id');
//$statement->execute(['id' => 1]); // SELECT * FROM urls WHERE id = 1