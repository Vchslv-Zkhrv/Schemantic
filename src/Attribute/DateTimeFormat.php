<?php

namespace Schemantic\Attribute;

use Attribute;

/**
 * Use to set a date/time format
 *
 * @category Library
 * @package  Schemantic\Attribute
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class DateTimeFormat
{
    /**
     * Format constructor.
     * You can define a common format for entire class and separate formats for specific fields
     *
     * Set `$format` = 'unix' to use integer unix epoch
     *
     * @param string $format string representation of value
     */
    public function __construct(public readonly string $format)
    {
    }
}
