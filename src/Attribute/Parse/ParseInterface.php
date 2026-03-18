<?php

namespace Schemantic\Attribute\Parse;

use Schemantic\Attribute\SingleAttributeInterface;
use Schemantic\SchemaInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Interface for parse attributes
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
interface ParseInterface extends SingleAttributeInterface
{
    /**
     * Parse value
     *
     * @param mixed                                  $value  raw value to parse from
     * @param ReflectionClass<SchemaInterface>       $schema schema asking to parse
     * @param ReflectionProperty|ReflectionParameter $field  field to parse into
     *
     * @return mixed
     */
    public function parse(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field,
    );
}
