<?php

namespace Schemantic\Tests\TestTypes;

use PHPUnit\Framework\TestCase;
use Schemantic\Type\DateTime\DateTimeRange;

class DateTimeRangeTest extends TestCase
{
    public function testFinishEarlierThanStart(): void
    {
        $this->expectException(\RangeException::class);

        $range = new DateTimeRange(
            new \DateTimeImmutable('now'),
            new \DateTimeImmutable('-1 days')
        );
    }

    public function testInterval(): void
    {
        $range = new DateTimeRange(
            new \DateTimeImmutable('now'),
            new \DateTimeImmutable('+1 week')
        );

        $this->assertEquals(7*24, $range->getLenght('hour'));
        $this->assertEquals(7*24*60, $range->getLenght('min'));
        $this->assertEquals(7*24*60*60, $range->getLenght('sec'));
        $this->assertEquals(7*24*60*60*100_000, $range->getLenght('us'));
    }

    public function testGetSteps(): void
    {
        $now = new \DateTimeImmutable('now');
        $range = new DateTimeRange(
            $now,
            $now->add(\DateInterval::createFromDateString('+1 week +4 hour +12 min'))
        );

        $steps = $range->getSteps(unit:'min');

        $this->assertCount(7*24*60 + 4*60 + 12 + 1, $steps);

        $all = true;
        for ($i=0; $i<count($steps)-1; $i++) {
            if ($steps[$i] > $steps[$i+1]) {
                $all = false;
                break;
            }
        }
        $this->assertTrue($all);

        $this->assertEquals($range->getStart(), $steps[0]);
        $this->assertEquals($range->getFinish(), end($steps));
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }
}
