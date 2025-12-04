<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Schema;

class GetUserSchema extends Schema
{
    public function __construct(
        #[Alias('fname')]
        public string $firstname,

        #[Alias('lname')]
        public string $lastname,

        public int $age,

        public bool $isActive
    ) {
    }
}
