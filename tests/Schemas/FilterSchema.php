<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;
use Schemantic\Tests\Objects\StatusEnum;

class FilterSchema extends Schema
{

    /**
     * @param int[] $ids
     * @param \Schemantic\Tests\Schemas\Tag[] $tags
     */
    public function __construct(

        public array $ids,
        public bool $strict,
        public array $tags,
        public \DateTime $dateTo,
        public \DateTimeImmutable $dateFrom,
        public ?StatusEnum $status = null,

    ) { }

}
