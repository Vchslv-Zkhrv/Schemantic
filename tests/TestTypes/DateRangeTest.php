<?php

namespace Schemantic\Tests\TestTypes;

use PHPUnit\Framework\TestCase;
use Schemantic\Type\Date\DateRange;
use Schemantic\Type\Date\DateImmutable;

class DateRangeTest extends TestCase
{
    public function testFinishEarlierThanStart(): void
    {
        $this->expectException(\RangeException::class);

        $range = new DateRange(
            new DateImmutable('now'),
            new DateImmutable('-1 days')
        );
    }

    public function testInterval(): void
    {
        $range = new DateRange(
            new DateImmutable('now'),
            new DateImmutable('+1 week +1 hour')
        );

        $this->assertEquals(7, $range->getLenght('day'));
        $this->assertEquals(7*24, $range->getLenght('hour'));
        $this->assertEquals(7*24*60, $range->getLenght('min'));
        $this->assertEquals(7*24*60*60, $range->getLenght('sec'));
        $this->assertEquals(7*24*60*60*100_000, $range->getLenght('us'));
    }

    public function testGetSteps(): void
    {
        $now = new DateImmutable('now');
        $range = new DateRange(
            $now,
            $now->add(\DateInterval::createFromDateString('+11 week + 1 day'))
        );

        $steps = $range->getSteps(unit:'day');

        $this->assertCount(7*11 + 1 + 1, $steps);

        $increasing = true;
        for ($i=0; $i<count($steps)-1; $i++) {
            if ($steps[$i] > $steps[$i+1]) {
                $increasing = false;
                break;
            }
        }
        $this->assertTrue($increasing);

        $isDate = true;
        for ($i=0; $i<count($steps)-1; $i++) {
            if (!$steps[$i] instanceof DateImmutable) {
                $isDate = false;
                break;
            }
        }
        $this->assertTrue($isDate);

        $this->assertEquals($range->getStart(), $steps[0]);
        $this->assertEquals($range->getFinish(), end($steps));
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }
}
