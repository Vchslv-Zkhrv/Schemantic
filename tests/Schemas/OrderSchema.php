<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Schema;

class OrderSchema extends Schema
{
    public float $total;

    #[Alias('created_at')]
    public \DateTimeImmutable $createdAt;

    #[Alias('user_id')]
    public int $userId;

    public ?int $id = null;

    public function __construct(
        float $total,
        \DateTimeImmutable $createdAt,
        int $userId,
        ?int $id = null,
    ) {
        $this->total = $total;
        $this->createdAt = $createdAt;
        $this->userId = $userId;
        $this->id = $id;
    }
}
