<?php

namespace Schemantic\Attribute\Dump;

use Attribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Ensure array will be dumped to array/JSON as structure
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class AsObject implements DumpInterface
{
    public function dump(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field
    ) {
        if (is_array($value)) {
            return (object)$value;
        }

        return $value;
    }
}

