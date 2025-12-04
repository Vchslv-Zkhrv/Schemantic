<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;
use Schemantic\Attribute\Parse;
use Schemantic\Attribute\Dump;

class SchemaWithCustomParseDump extends Schema
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public readonly int $id,

        #[Dump\Dumper('dumpTags')]
        #[Parse\Parser('parseTags')]
        public readonly array $tags,
    ) {
    }

    /**
     * @param string $tags
     *
     * @return string[]
     */
    public static function parseTags(string $tags): array
    {
        return explode('|', $tags);
    }

    /**
     * @param string[] $tags
     *
     * @return string
     */
    public static function dumpTags(array $tags): string
    {
        return implode('|', $tags);
    }
}
