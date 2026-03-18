<?php

namespace Schemantic\Attribute;

use Attribute;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Schemantic\Exception\DumpingException;
use Schemantic\Exception\SchemaException;

/**
 * Use to mark datetime field as timestamp
 *
 * @category Library
 * @package  Schemantic\Attribute
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY|Attribute::TARGET_CLASS)]
class Timestamp implements DateTimeAttributeInterface
{
    /**
     * Timestamp constructor.
     * You can apply this attribute to entire class to mark all it's fields as timestamp
     *
     * @param int  $precision number of digits after zero
     * @param bool $asFloat   if `false`, the value will be increased by `$precision` times.
     */
    public function __construct(
        public readonly int $precision = 0,
        public readonly bool $asFloat = false
    ) {
        if ($precision < 0) {
            throw new SchemaException("Precision must not be less than 0");
        }
        if ($precision > 6) {
            throw new SchemaException("Precision must not be greater than 6");
        }
    }

    public function dump(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field
    ) {
        if ($value instanceof \DateTimeInterface) {
            $timestamp = $value->getTimestamp();
            if ($this->precision == 0) {
                if ($this->asFloat) {
                    return (float)$timestamp;
                } else {
                    return $timestamp;
                }
            } else {
                $microseconds = (int)$value->format('u');
                if ($this->precision < 6) {
                    $microseconds = (int)round($microseconds / pow(10, 6-$this->precision));
                }
                if ($this->asFloat) {
                    return (float)"{$timestamp}.{$microseconds}";
                } else {
                    return (int)"{$timestamp}{$microseconds}";
                }
            }
        }

        throw new DumpingException("Value is not a datetime");
    }

    public function parse(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field
    ) {
        $type = $field->getType();
        $class = DateTimeImmutable::class;
        if ($type instanceof ReflectionUnionType) {
            $classes = array_filter(
                $type->getTypes(),
                fn (ReflectionType $t) => $t->__toString() instanceof DateTimeInterface
            );
            if ($classes) {
                $class = reset($classes);
            }
        } else if ($type instanceof ReflectionType) {
            $class = $type->__toString();
        }

        if ($this->precision == 0) {
            $value = (int)$value;
            return $class::createFromFormat(
                'Y-m-d H:i:s',
                (new DateTime())->setTimestamp($value)->format('Y-m-d H:i:s')
            );
        } else {
            if ($this->asFloat) {
                $value = number_format(round((float)$value, $this->precision), 6, '.', '');
                $parts = explode(".", (string)$value);
                $timestamp = ($parts[0]);
                $microseconds = ($parts[1] ?? 0);
            } else {
                $timestamp = substr((string)$value, 0, 0-$this->precision);
                $microseconds = substr((string)$value, 0-$this->precision);
            }
            $microseconds = str_pad($microseconds, 6, '0', STR_PAD_RIGHT);
            return $class::createFromFormat(
                'Y-m-d H:i:s.u',
                (new DateTime())->setTimestamp((int)$timestamp)->format('Y-m-d H:i:s.') . $microseconds
            );
        }
    }
}
