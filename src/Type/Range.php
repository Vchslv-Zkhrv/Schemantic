<?php

namespace Schemantic\Type;

use DateInterval;
use DateTimeImmutable;
use Schemantic\Schema;

/**
 * A period with clearly defined boundaries
 *
 * @category Library
 * @package  Schemantic\Type
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
abstract class Range extends Schema
{
    /**
     * Returns separator used to parse/dump strings.
     *
     * Example: `2025-01-01...2025-01-07`
     *
     * @return string
     */
    public static function getSeparator(): string
    {
        return '...';
    }

    /**
     * Override to set field validations
     *
     * Example: ` [ 'password' => (mb_strlen($this->password) > 7) ]`
     *
     * @return array<string,bool> `{field_name: validation}`
     */
    public function getValidations(): array
    {
        return [
            'start' => $this->start <= $this->finish
        ];
    }

    /**
     * @param DateTimeImmutable $start  start of period
     * @param DateTimeImmutable $finish end of period
     *
     * @throws \RangeException
     */
    public function __construct(
        protected DateTimeImmutable $start,
        protected DateTimeImmutable $finish
    ) {
        if (!$this->validate(false)) {
            throw new \RangeException(
                "Finish of the range cannot be earlier than start"
            );
        }
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStart(): DateTimeImmutable
    {
        return $this->start;
    }

    /**
     * Returns copy with new $start
     *
     * @param \DateTimeImmutable $start new start
     *
     * @return static
     * @throws \RangeException
     */
    public function setStart(DateTimeImmutable $start): static
    {
        return new static($start, $this->finish); // @phpstan-ignore-line
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFinish(): DateTimeImmutable
    {
        return $this->finish;
    }

    /**
     * Returns copy with new $finish
     *
     * @param \DateTimeImmutable $finish new finish
     *
     * @return static
     * @throws \RangeException
     */
    public function setFinish(DateTimeImmutable $finish): static
    {
        return new static($this->start, $finish); // @phpstan-ignore-line
    }

    /**
     * @param 'year'|'month'|'day'|'hour'|'min'|'sec'|'us' $unit unit
     *
     * @return int
     *
     * @throws \ValueError
     */
    public function getLenght(string $unit = 'day'): int
    {
        $diff = $this->finish->diff($this->start);

        if ($unit == 'year') {
            return $diff->y;
        }

        if ($unit == 'month') {
            return $diff->y*12 + $diff->m;
        }

        $len = (int)$diff->days;

        if ($unit == 'day') {
            return $len;
        }

        $len = $len*24 + $diff->h;

        if ($unit == 'hour') {
            return $len;
        }

        $len = $len*60 + $diff->i;

        if ($unit == 'min') {
            return $len;
        }

        $len = $len*60 + $diff->s;

        if ($unit == 'sec') {
            return $len;
        }

        $len = $len*100_000 + (int)$diff->f;

        if ($unit == 'us') {
            return $len;
        }

        throw new \ValueError("Invalid unit '$unit'");
    }

    /**
     * @throws \ValueError
     * @return \DateInterval
     */
    public function getInterval(): DateInterval
    {
        $interval = $this->start->diff($this->finish);

        if (!$interval) {
            throw new \ValueError("Failed to calculate interval");
        }

        return $interval;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $format = static::getDateTimeFormat();
        return '' .
            $this->start->format($format) .
            static::getSeparator() .
            $this->finish->format($format)
        ;
    }

    /**
     * @param string $format date/time/datetime format
     *
     * @return string '$start->format($format)' AND '$finish->format($format)'
     */
    public function toSQL(string $format = 'Y-m-d H:i:s'): string
    {
        return '' .
            $this->start->format($format) .
            ' AND ' .
            $this->finish->format($format)
        ;
    }

    /**
     * Creates array with datetimes between start and finish
     * (including start and, if possible, finish)
     *
     * @param int                                          $step in units
     * @param 'year'|'month'|'day'|'hour'|'min'|'sec'|'us' $unit unit
     *
     * @return array<DateTimeImmutable>
     */
    public function getSteps(
        int $step = 1,
        string $unit = 'day'
    ): array {
        if ($step <= 0) {
            throw new \ValueError('Step cannot be less or equals 0');
        }

        $current = DateTimeImmutable::createFromInterface($this->start);
        $interval = DateInterval::createFromDateString("+$step $unit");
        $steps = [];

        while ($current <= $this->finish) {
            $steps[] = $current;
            $current = $current->add($interval);
        }

        return $steps;
    }
}
