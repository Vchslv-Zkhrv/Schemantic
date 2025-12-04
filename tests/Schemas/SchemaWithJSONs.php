<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;
use Schemantic\Attribute\Parse;
use Schemantic\Attribute\Dump;
use Schemantic\Attribute\Group;

class SchemaWithJSONs extends Schema
{
    /**
     * @param array<string,mixed> $value
     */
    public function __construct(
        public readonly int $id,

        #[Group('jsonValue', new Parse\JSON, new Dump\JSON)]
        public readonly array $value,
    ) {
    }
}
