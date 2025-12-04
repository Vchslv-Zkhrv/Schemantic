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
     * @param 'ACTIVE'|'INACTIVE'|'BLOCKED' $status
     */
    public function __construct(
        #[GreaterThan(17)]
        #[LowerThan(100)]
        public int $age,

        #[OneOf(['ACTIVE', 'INACTIVE', 'BLOCKED'])]
        public string $status,

        #[Alias('user_id')]
        public int $userId
    ) {
    }
}
