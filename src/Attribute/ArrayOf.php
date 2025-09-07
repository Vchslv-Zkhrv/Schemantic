<?php

namespace Schemantic\Attribute;

use Attribute;
use Schemantic\SchemaInterface;

/**
 * Use to mark that array field must be parsed as array of substructures
 *
 * @category Library
 * @package  Schemantic\Attribute
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class ArrayOf
{
    /**
     * ArrayOf constructor
     *
     * @param class-string<SchemaInterface> $class item type class
     */
    public function __construct(public readonly string $class)
    {
    }
}
