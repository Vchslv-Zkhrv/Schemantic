<?php

namespace Schemantic\Attribute\Chrono;

use Schemantic\Attribute\Parse\ParseInterface;
use Schemantic\Attribute\Dump\DumpInterface;

/**
 * Interface for datetime-related attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Chrono
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface DateTimeAttributeInterface extends DumpInterface, ParseInterface
{
}
