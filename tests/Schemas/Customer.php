<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Attribute\Validate\GreaterThan;
use Schemantic\Attribute\Validate\LowerThan;
use Schemantic\Attribute\Validate\OneOf;
use Schemantic\Schema;

class Customer extends Schema
{
    /**
     * @var 'ACTIVE'|'INACTIVE'|'BLOCKED' $status
     */
    #[OneOf(['ACTIVE', 'INACTIVE', 'BLOCKED'])]
    public string $status;

    #[GreaterThan(17)]
    #[LowerThan(100)]
    public int $age;

    #[Alias('user_id')]
    public int $userId;

    /**
     * @param 'ACTIVE'|'INACTIVE'|'BLOCKED' $status
     */
    public function __construct(
        int $age,
        string $status,
        int $userId
    ) {
        $this->age = $age;
        $this->status = $status;
        $this->userId = $userId;
    }
}
