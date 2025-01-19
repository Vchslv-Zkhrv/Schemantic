<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;

class Customer extends Schema
{
    /** @var 'ACTIVE'|'INACTIVE'|'BLOCKED' $status */
    public string $status;
    public int $age;
    public int $userId;

    public function getValidations(): array
    {
        return [
            'status' => in_array($this->status, ['ACTIVE', 'INACTIVE', 'BLOCKED']),
            'age'    => $this->age > 17 && $this->age < 100
        ];
    }

    public static function getAliases(): array
    {
        return [
            'userId' => 'user_id'
        ];
    }

    /**
     * @param 'ACTIVE'|'INACTIVE'|'BLOCKED' $status
     */
    public function __construct(
        int $age,
        string $status,
        int $userId
    )
    {
        $this->age = $age;
        $this->status = $status;
        $this->userId = $userId;
    }
}
