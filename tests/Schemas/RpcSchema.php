<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Propagate;
use Schemantic\Schema;

class RpcSchema extends Schema
{
    public function __construct(
        public readonly string $id,

        #[Propagate]
        public readonly string $method,

        public readonly RpcParamsSchema $params,
    ) {
    }
}
