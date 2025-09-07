<?php
// phpcs:ignoreFile

namespace Schemantic\Tests\Schemas;

use Schemantic\Schema;

class Tag extends Schema
{
    public string $name;
    public ?string $icon;

    public function __construct(
        string $name,
        ?string $icon = null,
    ) {
        $this->name = $name;
        $this->icon = $icon;
    }
}
