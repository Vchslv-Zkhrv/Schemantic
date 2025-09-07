<?php

namespace Schemantic\Attribute\Validate;

use Schemantic\SchemaInterface;

/**
 * Abstract base class for validation attributes
 *
 * @template T
 *
 * @category Library
 * @package  Schemantic\Attribute\Validate
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
abstract class BaseValidation
{
    /**
     * Performs validation
     *
     * @param T               $value  property value
     * @param SchemaInterface $schema schema to check
     *
     * @return bool
     */
    abstract public function check($value, SchemaInterface $schema): bool;

    /**
     * Generates pretty string implementation of any value
     *
     * @param T $value property value
     *
     * @return string
     */
    protected function stringify($value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_string($value)) {
            return "'$value'";
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if ($value instanceof \UnitEnum) {
            return $value->name;
        }
        if (is_object($value)) {
            return $value::class . json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        }
        return (string)$value;
    }

    /**
     * Generates error pretty string implementation
     *
     * @param T $value property value
     *
     * @return string
     */
    abstract public function getErrorMessage($value): string;
}
