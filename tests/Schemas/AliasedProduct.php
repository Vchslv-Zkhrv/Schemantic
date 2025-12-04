<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;

class AliasedProduct extends Product
{
    public function __construct(
        string $name,

        #[Alias('total')]
        float $price,

        array $tags,

        #[Alias('info')]
        string $description = ''
    ) {
        parent::__construct($name, $price, $tags, $description);
    }
}
