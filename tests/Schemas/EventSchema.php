<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\DateTimeFormat;
use Schemantic\Schema;

class EventSchema extends Schema
{
    public function __construct(
        public readonly string $label,
        //
        #[DateTimeFormat('Y-m-d')]
        public readonly \DateTimeImmutable $date,
        //
        #[DateTimeFormat('H:i:s')]
        public readonly \DateTimeImmutable $start,
        //
        #[DateTimeFormat('H:i:s')]
        public readonly \DateTimeImmutable $end
    ) {
    }
}
