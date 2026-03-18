<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Group;
use Schemantic\Attribute\Timestamp;

class UnixEventSchema extends EventSchema
{
    public function __construct(
        string $label,

        #[Timestamp]
        #[Group('timestamp3', new Timestamp(0, false))]
        \DateTimeImmutable $date,

        #[Timestamp]
        #[Group('timestamp3', new Timestamp(3, false))]
        \DateTimeImmutable $start,

        #[Timestamp]
        #[Group('timestamp3', new Timestamp(3, true))]
        \DateTimeImmutable $end
    ) {
        parent::__construct(...func_get_args());
    }
}
