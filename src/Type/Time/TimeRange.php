<?php

namespace Schemantic\Type\Time;

use Schemantic\Type\Range;


/**
 * A date period with clearly defined boundaries
 *
 * @category Library
 * @package  Schemantic\Type\Time
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
class TimeRange extends Range
{
    /**
     * @param DateTimeImmutable $start  start of period
     * @param DateTimeImmutable $finish end of period
     *
     * @throws \RangeException
     */
    public function __construct(
        TimeImmutable $start,
        TimeImmutable $finish
    ) {
        parent::__construct(
            $start->setDate(1970, 1, 1),
            $finish->setDate(1970, 1, 1)
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $format = static::getTimeFormat();
        return '' .
            $this->start->format($format) .
            static::getSeparator() .
            $this->finish->format($format)
        ;
    }

    /**
     * @param string $format time format
     *
     * @return string '$start->format($format)' AND '$finish->format($format)'
     */
    public function toSQL(string $format = 'H:i:s'): string
    {
        return parent::toSQL($format);
    }

    /**
     * Creates array with datetimes between start and finish
     * (including start and, if possible, finish)
     *
     * @param int                                          $step in units
     * @param 'year'|'month'|'day'|'hour'|'min'|'sec'|'us' $unit unit
     *
     * @return TimeImmutable[]
     */
    public function getSteps(
        int $step = 1,
        string $unit = 'day'
    ): array {
        if ($step <= 0) {
            throw new \ValueError('Step cannot be less or equals 0');
        }

        $current = TimeImmutable::createFromInterface($this->start);
        $interval = \DateInterval::createFromDateString("+$step $unit");
        $steps = [];

        while ($current <= $this->finish) {
            $steps[] = $current;
            $current = $current->add($interval);
        }

        return $steps;
    }
}
