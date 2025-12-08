<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * Propagate property value to nested subchemas and arrays
 *
 * Subschema's own property values will overwrite propagated ones
 *
 * @category Library
 * @package  Schemantic\Attribute
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class Propagate implements SingleAttributeInterface
{
}
