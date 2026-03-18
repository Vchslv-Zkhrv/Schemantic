<?php

namespace Schemantic\Attribute\Parse;

use Attribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use Schemantic\Exception\SchemaException;

/**
 * Use to set a property parsing callback
 *
 * @category Library
 * @package  Schemantic\Attribute\Parse
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class Parser implements ParseInterface
{
    /**
     * @param object|string $parser schema method, parser instance or class
     * @param ?string       $method parser method name
     */
    public function __construct(
        public readonly object|string $parser,
        public readonly ?string $method = null,
    ) {
    }

    public function parse(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field,
    ) {
        $parser = $this->parser;
        $method = $this->method;
        $schemaClass = $schema->getName();

        if (is_object($parser)) {
            if ($method === null) {
                return $parser($value);
            } elseif (method_exists($parser, $method)) {
                return $parser->$method($value);
            } else {
                throw new SchemaException("$schemaClass - Parser has no such method: $method");
            }
        }

        if (class_exists($parser)) {
            if ($method === null) {
                throw new SchemaException("$schemaClass - No \$method parameter specified");
            } elseif (method_exists($parser, $method)) {
                return $parser::$method($value);
            } else {
                throw new SchemaException("$schemaClass - Parser has no such method: $method");
            }
        }

        if (method_exists($schemaClass, $parser)) {
            return $schemaClass::$parser($value);
        }

        return $parser($value);
    }
}
