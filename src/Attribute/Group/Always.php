<?php

namespace Schemantic\Attribute\Group;

use Attribute;

/**
 * Set of attributes that must be applied regardless of the selected group.
 *
 * Marked with Always, non-repetitive attributes will override grouped ones. Repetitivie merges.
 *
 * @category Library
 * @package  Schemantic\Attribute\Group
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::IS_REPEATABLE)]
class Always extends GroupAttribute
{
}
