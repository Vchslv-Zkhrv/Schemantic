<?php
// phpcs:ignoreFile

namespace Schemantic\Tests;

use PHPUnit\Framework\TestCase;
use Schemantic\Tests\Schemas\AliasedShema;

class AliasTest extends TestCase
{
    public function testCases(): void
    {
        $schema = new AliasedShema(42, true);

        $this->assertEquals(
            [
                'camel' => 'someValue',
                'pascal' => 'SomeValue',
                'snake' => 'some_value',
                'SNAKE' => 'SOME_VALUE',
                'kebab' =>'some-value',
                'KEBAB' => 'SOME-VALUE',
            ],
            AliasedShema::getFieldAliases('someValue')
        );

        $this->assertEquals(
            [
                'camel' => 'anotherValue',
                'pascal' => 'AnotherValue',
                'snake' => 'another_value',
                'SNAKE' => 'ANOTHER_VALUE',
                'kebab' =>'another-value',
                'KEBAB' => 'ANOTHER-VALUE',
            ],
            AliasedShema::getFieldAliases('another_value')
        );

        $this->assertEquals(['someValue', 'another_value'], $schema->getContructParams());
        $this->assertEquals(['someValue', 'anotherValue'], $schema->getContructParams(byAlias: true, group: 'camel'));
        $this->assertEquals(['SomeValue', 'AnotherValue'], $schema->getContructParams(byAlias: true, group: 'pascal'));
        $this->assertEquals(['some_value', 'another_value'], $schema->getContructParams(byAlias: true, group: 'snake'));
        $this->assertEquals(['SOME_VALUE', 'ANOTHER_VALUE'], $schema->getContructParams(byAlias: true, group: 'SNAKE'));
        $this->assertEquals(['some-value', 'another-value'], $schema->getContructParams(byAlias: true, group: 'kebab'));
        $this->assertEquals(['SOME-VALUE', 'ANOTHER-VALUE'], $schema->getContructParams(byAlias: true, group: 'KEBAB'));

    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }
}
