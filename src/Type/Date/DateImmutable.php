<?php
// phpcs:ignoreFile

namespace Schemantic\Type\Date;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReturnTypeWillChange;

/**
 * Just DateTimeImmutable. Use to set separate parse/dump formats in Schemas
 *
 * @category Library
 * @package  Schemantic\Type\Time
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
class DateImmutable extends DateTimeImmutable
{
    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public static function __set_state($array): static
    {
        return parent::__set_state($array);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function add(DateInterval $interval): static
    {
        return parent::add($interval);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public static function createFromFormat($format, $datetime, $timezone = null): static|bool
    {
        return parent::createFromFormat($format, $datetime, $timezone);

    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public static function createFromInterface(DateTimeInterface $object): static
    {
        return parent::createFromInterface($object);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public static function createFromTimestamp(int|float $timestamp): static
    {
        return parent::createFromTimestamp($timestamp);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function setMicrosecond(int $microsecond): static
    {
        return parent::setMicrosecond($microsecond);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function setTime($hour, $minute, $second = 0, $microsecond = 0): static
    {
        return parent::setTime($hour, $minute, $second, $microsecond);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function setDate($year, $month, $day): static
    {
        return parent::setDate($year, $month, $day);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function setTimestamp($timestamp): static
    {
        return parent::setTimestamp($timestamp);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function sub(DateInterval $interval): static
    {
        return parent::sub($interval);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function modify($modifier): static|false
    {
        return parent::modify($modifier);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function setISODate($year, $week, $dayOfWeek = 1): static
    {
        return parent::setISODate($year, $week, $dayOfWeek);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function setTimezone($timezone): static
    {
        return parent::setTimezone($timezone);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public static function createFromMutable(DateTime $object): static
    {
        return parent::createFromMutable($object);
    }
}
