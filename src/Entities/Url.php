<?php

namespace Hexlet\Code\Entities;

use Carbon\Carbon;

class Url
{
    private ?int $id = null;
    private string $urlName;
    private string $createdAt;

    public function __construct(string $urlName)
    {
        $this->urlName = $urlName;
        $this->createdAt = Carbon::now()->toDateTimeString();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlName(): string
    {
        return $this->urlName;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUrlName(string $urlName): void
    {
        $this->urlName = $urlName;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public static function fromArray(array $urlData): Url
    {
        [$urlName, $createdAt] = $urlData;
        $url = new Url($urlName);
        $url->setUrlName($urlName);
        $url->setCreatedAt($createdAt);
        return $url;
    }
}
