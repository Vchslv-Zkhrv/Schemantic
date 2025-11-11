<?php

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\ArrayOf;
use Schemantic\Schema;
use Schemantic\Tests\Objects\StatusEnum;

class SchemaWithUnionTypes extends Schema
{
    /**
     * @param Tag|Tag[] $arrayofAndSchema1
     * @param Tag|Tag[] $arrayofAndSchema2
     */
    public function __construct(
        public readonly int|array|null $builtins1,
        public readonly int|array|null $builtins2,
        public readonly int|array|null $builtins3,
        public readonly float|StatusEnum $builtinAndEnum1,
        public readonly float|StatusEnum $builtinAndEnum2,
        public readonly float|\DateTimeImmutable $builtinAndDate1,
        public readonly float|\DateTimeImmutable $builtinAndDate2,
        public readonly string|Tag $builtinAndSchema1,
        public readonly string|Tag $builtinAndSchema2,
        #[ArrayOf(Tag::class)]
        public readonly array|Tag $arrayofAndSchema1,
        #[ArrayOf(Tag::class)]
        public readonly array|Tag $arrayofAndSchema2,
        public readonly Tag|StatusEnum $schemaAndEnum1,
        public readonly Tag|StatusEnum $schemaAndEnum2,
        // types order is important
        public readonly Product|Tag $schemaAndSchema1,
        public readonly Product|Tag $schemaAndSchema2,
    ) {
    }
}
