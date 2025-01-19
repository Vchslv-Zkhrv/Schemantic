<?php

namespace Schemantic\Type\Date;

use Schemantic\Type\Range;

/**
 * A date period with clearly defined boundaries
 *
 * @category Library
 * @package  Schemantic\Type\Date
 * @author   Vyacheslav Zakharov <vchslv.zkhrv@gmail.com>
 * @license  opensource.org/license/mit MIT
 * @link     github.com/Vchslv-Zkhrv/Schemantic
 */
class DateRange extends Range
{
    /**
     * @param DateTimeImmutable $start  start of period
     * @param DateTimeImmutable $finish end of period
     *
     * @throws \RangeException
     */
    public function __construct(
        DateImmutable $start,
        DateImmutable $finish
    ) {
        parent::__construct(
            $start->setTime(0, 0, 0, 0),
            $finish->setTime(0, 0, 0, 0)
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $format = static::getDateFormat();
        return '' .
            $this->start->format($format) .
            static::getSeparator() .
            $this->finish->format($format)
        ;
    }

    /**
     * @param string $format date format
     *
     * @return string '$start->format($format)' AND '$finish->format($format)'
     */
    public function toSQL(string $format = 'Y-m-d'): string
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
     * @return DateImmutable[]
     */
    public function getSteps(
        int $step = 1,
        string $unit = 'day'
    ): array {
        if ($step <= 0) {
            throw new \ValueError('Step cannot be less or equals 0');
        }

        $current = DateImmutable::createFromInterface($this->start);
        $interval = \DateInterval::createFromDateString("+$step $unit");
        $steps = [];

        while ($current <= $this->finish) {
            $steps[] = $current;
            $current = $current->add($interval);
        }

        return $steps;
    }
}
