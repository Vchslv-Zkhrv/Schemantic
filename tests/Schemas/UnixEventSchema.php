<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\DateTimeFormat;

class UnixEventSchema extends EventSchema
{
    #[DateTimeFormat('Y-m-d')]
    public readonly \DateTimeImmutable $date;

    #[DateTimeFormat('H:i:s')]
    public readonly \DateTimeImmutable $start;

    #[DateTimeFormat('H:i:s')]
    public readonly \DateTimeImmutable $end;
}
