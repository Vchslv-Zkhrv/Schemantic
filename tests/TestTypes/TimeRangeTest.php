<?php

namespace Schemantic\Tests\TestTypes;

use PHPUnit\Framework\TestCase;
use Schemantic\Type\Time\TimeRange;
use Schemantic\Type\Time\TimeImmutable;

class TimeRangeTest extends TestCase
{
    public function testFinishEarlierThanStart(): void
    {
        $this->expectException(\RangeException::class);

        $range = new TimeRange(
            new TimeImmutable('now'),
            new TimeImmutable('-1 hours')
        );
    }

    public function testInterval(): void
    {
        $range = new TimeRange(
            new TimeImmutable('now'),
            new TimeImmutable('+1 week')
        );

        $this->assertEquals(0, $range->getLenght('hour'));
        $this->assertEquals(0, $range->getLenght('min'));
        $this->assertEquals(0, $range->getLenght('sec'));
        $this->assertEquals(0, $range->getLenght('us'));
    }

    public function testGetSteps(): void
    {
        $now = new TimeImmutable('now');
        $range = new TimeRange(
            $now,
            $now->add(\DateInterval::createFromDateString('+19 min +3 sec'))
        );

        $steps = $range->getSteps(unit:'sec');

        $this->assertCount(19*60 + 3 + 1, $steps);

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
            if (!$steps[$i] instanceof TimeImmutable) {
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
