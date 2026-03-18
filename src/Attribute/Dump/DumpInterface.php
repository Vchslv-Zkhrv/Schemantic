<?php

namespace Schemantic\Attribute\Dump;

use Schemantic\Attribute\SingleAttributeInterface;
use Schemantic\SchemaInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Interface for dump attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Dump
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface DumpInterface extends SingleAttributeInterface
{
    /**
     * Dump value
     *
     * @param mixed                                  $value  raw value to dump from
     * @param ReflectionClass<SchemaInterface>       $schema schema asking for dump
     * @param ReflectionProperty|ReflectionParameter $field  field to dump from
     *
     * @return mixed
     */
    public function dump(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field,
    );
}
