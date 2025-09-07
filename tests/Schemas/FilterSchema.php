<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\ArrayOf;
use Schemantic\Schema;
use Schemantic\Tests\Objects\StatusEnum;

class FilterSchema extends Schema
{
    /**
     * @param int[] $ids
     * @param Tag[] $tags
     */
    public function __construct(
        public array $ids,
        public bool $strict,
        #[ArrayOf(Tag::class)]
        public array $tags,
        public \DateTime $dateTo,
        public \DateTimeImmutable $dateFrom,
        public ?StatusEnum $status = null,
    ) {
    }
}
