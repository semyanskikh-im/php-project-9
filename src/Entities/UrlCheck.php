<?php

namespace Hexlet\Code\Entities;

class UrlCheck
{
    private ?int $id = null;
    private int $urlId;
    private int $statusCode;
    private string $h1;
    private string $title;
    private string $description;
    private ?string $createdAt = null;

    public function __construct(
        int $urlId,
        int $statusCode,
        string $h1,
        string $title,
        string $description
    ) {
        $this->urlId = $urlId;
        $this->statusCode = $statusCode;
        $this->h1 = $h1;
        $this->title = $title;
        $this->description = $description;
    }

    public function getUrlId(): int
    {
        return $this->urlId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getH1(): string
    {
        return $this->h1;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUrlId(int $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setH1(string $h1): void
    {
        $this->h1 = $h1;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $checkData): UrlCheck
    {
        [$urlId, $statusCode, $h1, $title, $description, $createdAt] = $checkData;
        $check = new UrlCheck($urlId, $statusCode, $h1, $title, $description);
        $check->setCreatedAt($createdAt);
        return $check;
    }
}
