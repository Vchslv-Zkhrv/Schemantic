<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;
use Schemantic\Attribute\Group;
use Schemantic\Attribute\Alias;

#[Group\Group('camel',  new Alias\CamelCase)]
#[Group\Group('pascal', new Alias\PascalCase)]
#[Group\Group('snake',  new Alias\SnakeCase)]
#[Group\Group('SNAKE',  new Alias\SnakeCase(true))]
#[Group\Group('kebab',  new Alias\KebabCase)]
#[Group\Group('KEBAB',  new Alias\KebabCase(true))]
class AliasedShema extends Schema
{
    public function __construct(
        public readonly int $someValue,

        public readonly bool $another_value,
    ) {
    }
}
