<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * Use to set a `__construct` param alias
 *
 * @category Library
 * @package  Schemantic\Attribute
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class Alias implements SingleAttributeInterface
{
    /**
     * Alias constructor. Last applied attribute value will be used al alias
     *
     * @param string $alias alternative name for this field
     */
    public function __construct(public readonly string $alias)
    {
    }
}
