<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;

class RpcParamsSchema extends Schema
{
    public function __construct(
        public readonly string $method,
        public readonly \DateTimeInterface $from,
        public readonly \DateTimeInterface $to,
    ) {
    }
}
