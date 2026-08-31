<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Group\Group;
use Schemantic\Attribute\Timestamp;

#[Timestamp]
class UnixEventSchema extends EventSchema
{
    public function __construct(
        string $label,

        #[Group('timestamp3', new Timestamp(0, false))]
        \DateTimeImmutable $date,

        #[Group('timestamp3', new Timestamp(3, false))]
        \DateTimeImmutable $start,

        #[Group('timestamp3', new Timestamp(3, true))]
        \DateTimeImmutable $end
    ) {
        parent::__construct(...func_get_args());
    }
}
