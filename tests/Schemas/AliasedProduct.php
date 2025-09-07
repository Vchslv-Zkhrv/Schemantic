<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\Alias;

class AliasedProduct extends Product
{
    #[Alias('total')]
    public float  $price;

    #[Alias('info')]
    public string $description = '';
}
