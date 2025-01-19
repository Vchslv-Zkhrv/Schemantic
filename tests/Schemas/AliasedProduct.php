<?php

namespace Schemantic\Tests\Schemas;

class AliasedProduct extends Product
{
    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
       return [
            'price' => 'total',
            'description' => 'info'
       ];
    }
}
