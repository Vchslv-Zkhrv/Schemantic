<?php
// phpcs:ignoreFile

namespace Schemantic\Tests;

use PHPUnit\Framework\TestCase;
use Schemantic\Tests\Objects\StatusEnum;
use Schemantic\Tests\Schemas\IfNotSetSchema;
use Schemantic\Tests\Schemas\IfSetSchema;
use Schemantic\Tests\Schemas\ValidatingSchema;

class ValidationTest extends TestCase
{
    /**
     * @return array<string,mixed>
     */
    private function getValidArray(): array
    {
        return [
            'valueContains' => [ 'foo', 'bar', 'spam', 'test', ],
            'valueGreaterThan' => 20,
            'valueGreaterThanOrEquals' => new \DateTimeImmutable('2025-01-02 00:00:00'),
            'valueHasNo' => [ 'active', 'banned', 'left'],
            'valueArrayLength' => [1, 2, 3, 4, 5, 6, 7, 8],
            'valueStringLength' => 'abcdefgh',
            'valueLowerThan' => new \DateTimeImmutable('yesterday'),
            'valueLowerThanOrEquals' => 100,
            'valueNotEmpty' => 'a',
            'valueNotIn' => 9,
            'valueNotNull' => '',
            'valueOnOf' => 'banned',
            'valuePlain' => ['a', 'b', 'c'],
            'valueValidate' => '12345678901234567890',
            'valueManyValidations' => 19,
            'valueExactly' => StatusEnum::ACTIVE,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function getInvalidArray(): array
    {
        return [
            'valueContains' => [ 'foo', 'spam', 'test', ],
            'valueGreaterThan' => 10,
            'valueGreaterThanOrEquals' => new \DateTimeImmutable('2024-01-01 00:00:00'),
            'valueHasNo' => [ 'active', 'banned', 'left', 'deleted'],
            'valueArrayLength' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'valueStringLength' => 'abc',
            'valueLowerThan' => new \DateTimeImmutable('tomorrow'),
            'valueLowerThanOrEquals' => 101,
            'valueNotEmpty' => '',
            'valueNotIn' => 2,
            'valueNotNull' => null,
            'valueOnOf' => 'foo',
            'valuePlain' => ['a', 'b', 'c', ['d', 'e', 'f']],
            'valueValidate' => 'aaaaaaaaaaa',
            'valueManyValidations' => 20,
            'valueExactly' => StatusEnum::BANNED,
        ];
    }

    public function testValidArray(): void
    {
        $validArray = $this->getValidArray();
        $schema = ValidatingSchema::fromArray($validArray);

        $this->assertEquals($validArray, $schema->toArray());
    }

    public function testInvalidArray(): void
    {
        $validArray = $this->getValidArray();
        $validSchema = ValidatingSchema::fromArray($validArray);
        $invalidArray = $this->getInvalidArray();

        foreach ($invalidArray as $key => $invalidValue) {
            $schema = clone $validSchema;
            $schema->$key = $invalidValue;
            $fails = $schema->validate(getFails: true);
            $this->assertCount(1, $fails);
            $this->assertArrayHasKey($key, $fails);
        }
    }

    public function testIfSet(): void
    {
        $valid = new IfSetSchema('a', 'b');
        $valid = new IfSetSchema('a', null);
        $valid = new IfSetSchema(null, null);

        $invalid = new IfSetSchema(null, 'b');
        $fails = $invalid->validate(getFails: true, byAlias: false);
        $this->assertArrayHasKey('childValue', $fails);
        $this->assertCount(1, $fails);
        $this->assertCount(1, reset($fails));
        $this->assertEquals('field `parentValue` is empty', $fails['childValue'][0]);

        $fails = $invalid->validate(getFails: true, byAlias: true);
        $this->assertArrayHasKey('child', $fails);
        $this->assertCount(1, $fails);
        $this->assertCount(1, reset($fails));
        $this->assertEquals('field `parent` is empty', $fails['child'][0]);

        $fails = $invalid->validate(getFails: true, byAlias: true, group: 'zip');
        $this->assertArrayHasKey('c', $fails);
        $this->assertCount(1, $fails);
        $this->assertCount(1, reset($fails));
        $this->assertEquals('field `p` is empty', $fails['c'][0]);
    }

    public function testIfNotSet(): void
    {
        $valid = new IfNotSetSchema(null, 'b');
        $valid = new IfNotSetSchema(null, null);

        $invalid = new IfNotSetSchema('a', 'b');
        $fails = $invalid->validate(getFails: true, byAlias: false);
        $this->assertArrayHasKey('childValue', $fails);
        $this->assertCount(1, $fails);
        $this->assertCount(1, reset($fails));
        $this->assertEquals('field `parentValue` is not empty', $fails['childValue'][0]);

        $fails = $invalid->validate(getFails: true, byAlias: true);
        $this->assertArrayHasKey('child', $fails);
        $this->assertCount(1, $fails);
        $this->assertCount(1, reset($fails));
        $this->assertEquals('field `parent` is not empty', $fails['child'][0]);

        $fails = $invalid->validate(getFails: true, byAlias: true, group: 'zip');
        $this->assertArrayHasKey('c', $fails);
        $this->assertCount(1, $fails);
        $this->assertCount(1, reset($fails));
        $this->assertEquals('field `p` is not empty', $fails['c'][0]);
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }
}
