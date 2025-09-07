<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Attribute\ArrayOf;
use Schemantic\Schema;

class Product extends Schema
{
    /**
     * @var \Schemantic\Tests\Schemas\Tag[] $tags
     */
    public array  $tags;
    public string $name;
    public float  $price;
    public string $description = '';

    /**
     * @param \Schemantic\Tests\Schemas\Tag[] $tags
     */
    public function __construct(
        string $name,
        float  $price,
        #[ArrayOf(Tag::class)]
        array  $tags,
        string $description = '',
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->tags = $tags;
        $this->description = $description;
    }
}
