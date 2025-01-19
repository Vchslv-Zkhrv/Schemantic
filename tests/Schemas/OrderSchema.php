<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;

class OrderSchema extends Schema
{
    public float $total;
    public \DateTimeImmutable $createdAt;
    public int $userId;
    public ?int $id = null;

    /**
     * @return array<string, string>
     */
    public static function  getAliases(): array
    {
        return [
            'userId' => 'user_id',
            'createdAt' => 'created_at'
        ];
    }

    public function __construct(
        float $total,
        \DateTimeImmutable $createdAt,
        int $userId,
        ?int $id = null,
    )
    {
        $this->total = $total;
        $this->createdAt = $createdAt;
        $this->userId = $userId;
        $this->id = $id;
    }
}
