<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;
use Schemantic\Attribute\Chrono;
use Schemantic\Attribute\Group;
use Schemantic\Attribute\Validate;
use Schemantic\Schema;

#[Group\Group('input',
    new Chrono\DateTimeFormat('Y-m-d\TH:i:s.u'),
)]
#[Group\Group('output',
    new Chrono\Timestamp(),
)]
#[Chrono\DateTimeFormat('Y-m-d H:i:s')]
class SchemaWithGroups extends Schema
{
    public function __construct(
        #[Group\Group('input', new Alias('dt'))]
        #[Group\Group('output', new Alias('timestamp'))]
        public readonly ?\DateTimeImmutable $date,

        #[Validate\OneOf(['active', 'banned', 'unpaid', 'left'])]
        public readonly string $status,
    ) {
    }
}
