<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Schema;

class Env extends Schema
{
    public function __construct(
        #[Alias('APP_SECRET')]
        public string $secret,

        #[Alias('APP_ENV')]
        public string $mode,

        #[Alias('REDIS_TTL')]
        public int $ttl
    ) {
    }
}
