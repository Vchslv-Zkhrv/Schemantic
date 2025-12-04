<?php

namespace Schemantic\Attribute\Dump;

use Schemantic\Exception\SchemaException;

use Attribute;

/**
 * Use to set a property dumping callback
 *
 * @category Library
 * @package  Schemantic\Attribute\Dump
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class Dumper implements DumpInterface
{
    /**
     * @param object|string $dumper schema static method, dumper instance or class
     * @param ?string       $method dumper method name
     */
    public function __construct(
        public readonly object|string $dumper,
        public readonly ?string $method = null,
    ) {
    }

    public function dump($value, string $schema)
    {
        $dumper = $this->dumper;
        $method = $this->method;

        if (is_object($dumper)) {
            if ($method === null) {
                return $dumper($value);
            } elseif (method_exists($dumper, $method)) {
                return $dumper->$method($value);
            } else {
                throw new SchemaException("Dumper has no such method: $method");
            }
        }

        if (class_exists($dumper)) {
            if ($method === null) {
                throw new SchemaException("No \$method parameter specified");
            } elseif (method_exists($dumper, $method)) {
                return $dumper::$method($value);
            } else {
                throw new SchemaException("Dumper has no such method: $method");
            }
        }

        if (method_exists($schema, $dumper)) {
            return $schema::$dumper($value);
        }

        return $dumper($value);
    }
}

