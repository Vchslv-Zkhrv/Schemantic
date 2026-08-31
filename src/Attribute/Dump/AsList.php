<?php

namespace Schemantic\Attribute\Dump;

use Attribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Ensure array will be dumped to array/JSON as list
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class AsList implements DumpInterface
{
    public function dump(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field
    ) {
        if (is_object($value)) {
            $value = (array)$value;
        }

        if (is_array($value)) {
            return array_values($value);
        }

        return $value;
    }
}
