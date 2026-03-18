<?php

namespace Schemantic\Attribute;

use Attribute;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Schemantic\Exception\DateParsingException;
use Schemantic\Exception\DumpingException;

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
class DateTimeFormat implements DateTimeAttributeInterface
{
    /**
     * DateTimeFormat constructor.
     * You can define a common format for entire class and separate formats for specific fields
     *
     * @param string $format string representation of value
     */
    public function __construct(public readonly string $format)
    {
    }

    public function dump(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field,
    ) {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->format);
        }

        throw new DumpingException("value is not a datetime");
    }

    public function parse(
        $value,
        ReflectionClass $schema,
        ReflectionProperty|ReflectionParameter $field,
    ) {
        $value = (string)$value;
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
        $class = str_replace('?', '', $class);

        if ($value instanceof $class) {
            return $value;
        }

        if ($class == DateTimeInterface::class) {
            $class = DateTimeImmutable::class;
        }

        $dateTime = $class::createFromFormat($this->format, $value);
        if ($dateTime === false) {
            throw new DateParsingException("Cannot parse '$value' as $class with format = '$this->format'");
        }

        return $dateTime;
    }
}
