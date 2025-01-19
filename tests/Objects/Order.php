<?php

namespace Schemantic\Tests\Objects;

class Order
{
    private ?int $id = null;
    private \DateTimeImmutable $createdAt;
    private float $total;
    private int $userId;

    public function __construct(
        \DateTimeImmutable $createdAt,
        float $total,
        int $userId,
    )
    {
        $this->createdAt = $createdAt;
        $this->total = $total;
        $this->userId = $userId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
