<?php

namespace Hexlet\Code;

use Carbon\Carbon;

class Check
{
    private ?int $id = null;
    private ?int $urlId = null;
    private ?int $statusCode = null;
    private ?string $h1 = null;
    private ?string $title = null;
    private ?string $description = null;
    private string $createdAt;

    public function __construct()
    {
        $this->createdAt = Carbon::now()->toDateTimeString();
    }

    public function getUrlId(): ?int
    {
        return $this->urlId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTitle(): ?string
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

    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setH1(?string $h1): void
    {
        $this->h1 = $h1;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $checkData): Check
    {
        [$urlId, $statusCode, $h1, $title, $description, $createdAt] = $checkData;
        $check = new Check();
        $check->setUrlId($urlId);
        $check->setStatusCode($statusCode);
        $check->setH1($h1);
        $check->setTitle($title);
        $check->setDescription($description);
        $check->setCreatedAt($createdAt);
        return $check;
    }
}
