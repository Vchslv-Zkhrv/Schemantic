<?php

namespace Schemantic\Attribute\Alias;

use Attribute;

/**
 * Use to set a `__construct` param alias
 *
 * @category Library
 * @package  Schemantic\Attribute\Alias
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class Alias implements AliasInterface
{
    /**
     * Alias constructor
     *
     * @param string $alias alternative name for this field
     */
    public function __construct(public readonly string $alias)
    {
    }

    public function getAlias(string $name): string
    {
        return $this->alias;
    }
}
