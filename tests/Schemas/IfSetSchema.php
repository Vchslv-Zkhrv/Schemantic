<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Attribute\Group;
use Schemantic\Schema;
use Schemantic\Attribute\Validate;

class IfSetSchema extends Schema
{
    public function __construct(
        #[Group\ByDefault(
            new Alias('parent')
        )]
        #[Group\Group('zip',
            new Alias('p')
        )]
        public readonly ?string $parentValue = null,

        #[Group\ByDefault(
            new Alias('child'),
        )]
        #[Group\Group('zip',
            new Alias('c'),
        )]
        #[Group\Always(
            new Validate\IfSet('parentValue')
        )]
        public readonly ?string $childValue = null,
    ) {
    }
}
