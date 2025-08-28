<?php

namespace Hexlet\Code;

use PDO;
use Hexlet\Code\Url;

require 'vendor/autoload.php';

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

    private function create(Url $url): void
    {
        $sql = "INSERT INTO urls (name, created_at) VALUES (:name, :created_at)";
        $stmt = $this->pdo->prepare($sql);
        $urlName = $url->getUrlName();
        $createdAt = $url->getCreatedAt();
        $stmt->bindParam(':name', $urlName);
        $stmt->bindParam(':model', $createdAt);
        $stmt->execute();
        $id = (int) $this->pdo->lastInsertId();
        $url->setId($id);
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