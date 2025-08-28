<?php

namespace Hexlet\Code;

use Carbon\Carbon;
//require 'vendor/autoload.php';

class Url
{ 
    private ?int $id = null;
    private ?string $urlName = null;
    private ?Carbon $createdAt;
 
    public function __construct()
    {
        $this->createdAt = Carbon::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlName(): ?string
    {
        return $this->urlName;
    }

    public function getCreatedAt(): Carbon
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

    public function setCreatedAt(Carbon $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

     public static function fromArray(array $urlData): Url
    {
        [$urlName, $createdAt] = $urlData;
        $url = new Url();
        $url->setUrlName($urlName);
        $url->setCreatedAt($createdAt);
        return $url;
    }

    public function exists(): bool
    {
        return !is_null($this->getId());
    }

}