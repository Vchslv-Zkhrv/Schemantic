<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;
use Schemantic\Type\Date\DateImmutable;
use Schemantic\Type\Time\Time;

class EventSchema extends Schema
{
    public function __construct(
        public readonly string $label,
        public readonly DateImmutable $date,
        public readonly Time $start,
        public readonly Time $end
    ) { }
}
