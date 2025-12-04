<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Attribute\DateTimeFormat;
use Schemantic\Attribute\Group;
use Schemantic\Attribute\Validate;
use Schemantic\Schema;

#[Group('input',
    new DateTimeFormat('Y-m-d\TH:i:s.u'),
)]
#[Group('output',
    new DateTimeFormat('unix'),
)]
#[DateTimeFormat('Y-m-d H:i:s')]
class SchemaWithGroups extends Schema
{
    public function __construct(
        #[Group('input', new Alias('dt'))]
        #[Group('output', new Alias('timestamp'))]
        public readonly ?\DateTimeImmutable $date,

        #[Validate\OneOf(['active', 'banned', 'unpaid', 'left'])]
        public readonly string $status,
    ) {
    }
}
