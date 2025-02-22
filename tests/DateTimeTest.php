<?php

namespace Schemantic\Tests;

use PHPUnit\Framework\TestCase;
use Schemantic\Tests\Schemas\EventSchema;
use Schemantic\Type\Date\DateImmutable;
use Schemantic\Type\Time\Time;

class DateTimeTest extends TestCase
{
    public function testParseDefaultFormat(): void
    {
        $json = '{"label":"event","date":"2024-03-02","start":"12:00:00","end":"20:00:00"}';
        $schema = EventSchema::fromJSON($json);

        $this->assertEquals('event', $schema->label);
        $this->assertEquals('2024-03-02', $schema->date->format('Y-m-d'));
        $this->assertEquals('12:00:00', $schema->start->format('H:i:s'));
        $this->assertEquals('20:00:00', $schema->end->format('H:i:s'));
    }

    public function testParseCustomFormat(): void
    {
        $json = '{"label":"event","date":"02/03/2024","start":"12.00","end":"20.00"}';
        $schema = EventSchema::fromJSON(
            $json,
            dateFormat: 'd/m/Y',
            timeFormat: 'H.i'
        );

        $this->assertEquals('event', $schema->label);
        $this->assertEquals('2024-03-02', $schema->date->format('Y-m-d'));
        $this->assertEquals('12:00:00', $schema->start->format('H:i:s'));
        $this->assertEquals('20:00:00', $schema->end->format('H:i:s'));
    }

    public function testParseUnix(): void
    {
        $date = (new \DateTime('2024-03-02 00:00:00'))->getTimestamp();
        $start = (new \DateTime('2024-03-02 12:00:00'))->getTimestamp();
        $end = (new \DateTime('2024-03-02 20:00:00'))->getTimestamp();

        $json = '{"label":"event","date":'.$date.',"start":'.$start.',"end":'.$end.'}';
        $schema = EventSchema::fromJSON(
            $json,
            dateFormat: 'unix',
            timeFormat: 'unix'
        );

        $this->assertEquals('event', $schema->label);
        $this->assertEquals('2024-03-02', $schema->date->format('Y-m-d'));
        $this->assertEquals('12:00:00', $schema->start->format('H:i:s'));
        $this->assertEquals('20:00:00', $schema->end->format('H:i:s'));
    }

    public function testDumpDefaultFormat(): void
    {
        $schema = new EventSchema(
            'event',
            new DateImmutable('2024-03-02'),
            new Time('12:00:00'),
            new Time('20:00:00')
        );

        $this->assertEquals(
            '{"label":"event","date":"2024-03-02","start":"12:00:00","end":"20:00:00"}',
            $schema->toJSON()
        );
    }

    public function testDumpCustomFormat(): void
    {
        $schema = new EventSchema(
            'event',
            new DateImmutable('2024-03-02'),
            new Time('12:00:00'),
            new Time('20:00:00')
        );

        $this->assertEquals(
            '{"label":"event","date":"02\/03\/2024","start":"12.00","end":"20.00"}',
            $schema->toJSON(
                dateFormat: 'd/m/Y',
                timeFormat: 'H.i'
            )
        );
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }
}
