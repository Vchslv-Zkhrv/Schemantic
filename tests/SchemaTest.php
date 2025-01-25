<?php
// phpcs:ignoreFile

namespace Schemantic\Tests;

use PHPUnit\Framework\TestCase;
use Schemantic\Exception\SchemaException;
use Schemantic\Tests\Objects\User;
use Schemantic\Tests\Objects\ShortUser;
use Schemantic\Tests\Schemas\Product;
use Schemantic\Tests\Schemas\AliasedProduct;
use Schemantic\Tests\Schemas\Tag;
use Schemantic\Tests\Schemas\Env;
use Schemantic\Tests\Schemas\Customer;
use Schemantic\Tests\Schemas\OrderSchema;
use Schemantic\Tests\Schemas\GetUserSchema;
use Schemantic\Tests\Objects\StatusEnum;
use Schemantic\Tests\Schemas\FilterSchema;

class SchemaTest extends TestCase
{
    public function testConstructNumericArray(): void
    {
        $product = new Product(
            'name',
            1.5,
            [
                new Tag(name: 'tag1', icon: 'icon'),
                new Tag(name: 'tag2')
            ]
        );

        $this->assertEquals('name', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('', $product->description);
        $this->assertCount(2, $product->tags);

        $this->assertTrue(array_is_list($product->tags));
        $this->assertNull($product->tags[1]->icon);
    }

    public function testConstructAssociativeArray(): void
    {
        $product = new Product(
            'name',
            1.5,
            [
                'first' => new Tag(name: 'tag1', icon: 'icon'),
                'second' => new Tag(name: 'tag2')
            ]
        );

        $this->assertEquals('name', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('', $product->description);
        $this->assertCount(2, $product->tags);

        $this->assertFalse(array_is_list($product->tags));
        $this->assertNull($product->tags['second']->icon);
    }

    public function testFromArray(): void
    {
        $product = Product::fromArray([
            'name' => 'name',
            'price' => 1.5,
            'tags' => [
                'first' => [
                    'name' => 'tag1',
                    'icon' => 'icon'
                ],
                'second' => [
                    'name' => 'tag2'
                ]
            ]
        ]);

        $this->assertEquals('name', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('', $product->description);
        $this->assertCount(2, $product->tags);

        $this->assertFalse(array_is_list($product->tags));
        $this->assertInstanceOf(Tag::class, $product->tags['first']);
        $this->assertInstanceOf(Tag::class, $product->tags['second']);

        $this->assertEquals('icon', $product->tags['first']->icon);
        $this->assertNull($product->tags['second']->icon);
    }

    public function testFromArrayByAlias(): void
    {
        $product = AliasedProduct::fromArray([
            'name' => 'name',
            'total' => 1.5,
            'info' => 'info',
            'tags' => [
                'first' => [
                    'name' => 'tag1',
                    'icon' => 'icon'
                ],
                'second' => [
                    'name' => 'tag2'
                ]
            ]
        ], true);

        $this->assertEquals('name', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('info', $product->description);
        $this->assertCount(2, $product->tags);
    }

    public function testFromJSON(): void
    {
        $product = Product::fromJSON('{"name":"name","price":"1.5","tags":{"first":{"name":"tag1","icon":"icon"},"second":{"name":"tag2"}}}');

        $this->assertEquals('name', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('', $product->description);
        $this->assertCount(2, $product->tags);

        $this->assertFalse(array_is_list($product->tags));
        $this->assertInstanceOf(Tag::class, $product->tags['first']);
        $this->assertInstanceOf(Tag::class, $product->tags['second']);

        $this->assertEquals('icon', $product->tags['first']->icon);
        $this->assertNull($product->tags['second']->icon);
    }

    public function testFromJSONbyAlias(): void
    {
        $product = AliasedProduct::fromJSON(
            '{"name":"name","total":"1.5","tags":{"first":{"name":"tag1","icon":"icon"},"second":{"name":"tag2"}}}',
            byAlias: true
        );

        $this->assertEquals('name', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('', $product->description);
        $this->assertCount(2, $product->tags);
    }

    public function testFromJSONwithExtraFields(): void
    {
        $product = Product::fromJSON(
            '{"name":"name","price":"1.5","tags":{"first":{"name":"tag1","icon":"icon"},"second":{"name":"tag2"}}}',
            [ 'name' => 'test', 'description' => 'test' ]
        );

        $this->assertEquals('test', $product->name);
        $this->assertEquals(1.5, $product->price);
        $this->assertEquals('test', $product->description);
        $this->assertCount(2, $product->tags);

        $this->assertFalse(array_is_list($product->tags));
        $this->assertInstanceOf(Tag::class, $product->tags['first']);
        $this->assertInstanceOf(Tag::class, $product->tags['second']);

        $this->assertEquals('icon', $product->tags['first']->icon);
        $this->assertNull($product->tags['second']->icon);
    }

    public function testFromJSONwithInvaidSyntax(): void
    {
        $this->expectException(\JsonException::class);
        $product = Product::fromJSON('{"name":"name","price":"1.5","tags":{"first":{"name":"tag1","icon":"icon"},"second":{"name":"tag2"');
    }

    public function testIsPlain(): void
    {
        $this->assertFalse(Product::isPlain());
        $this->assertTrue(Tag::isPlain());
    }

    public function testToJSONByAlias(): void
    {
        $product = new AliasedProduct(
            'name',
            1.5,
            [
                new Tag(name: 'tag1', icon: 'icon'),
                new Tag(name: 'tag2')
            ]
        );

        $array = $product->toArray();
        $this->assertArrayHasKey('price', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayNotHasKey('info', $array);
        $this->assertArrayNotHasKey('total', $array);

        $aliasedArray = $product->toArray(byAlias: true);
        $this->assertArrayHasKey('total', $aliasedArray);
        $this->assertArrayHasKey('info', $aliasedArray);
        $this->assertArrayNotHasKey('price', $aliasedArray);
        $this->assertArrayNotHasKey('description', $aliasedArray);
    }

    public function testFromEnv(): void
    {
        putenv('APP_ENV=env');
        putenv('APP_SECRET=secret');
        putenv('REDIS_TTL=30');

        $env = Env::fromEnv();

        $this->assertEquals('env', $env->mode);
        $this->assertEquals('secret', $env->secret);
        $this->assertEquals(30, $env->ttl);
    }

    public function testFromObject(): void
    {
        $object = new User();
        $object->firstname = 'firstname';
        $object->lastname = 'lastname';
        $object->setActive(false);
        $object->birthday = new \DateTime('-21 years', new \DateTimeZone('UTC'));

        $schema = GetUserSchema::fromObject($object);

        $this->assertEquals('firstname', $schema->firstname);
        $this->assertEquals('lastname', $schema->lastname);
        $this->assertEquals(21, $schema->age);
        $this->assertEquals(false, $schema->isActive);
    }

    public function testFromObjectByAlias(): void
    {
        $object = new ShortUser();
        $object->fname = 'firstname';
        $object->lname = 'lastname';
        $object->setActive(false);
        $object->birthday = new \DateTime('-21 years', new \DateTimeZone('UTC'));

        $schema = GetUserSchema::fromObject($object, byAlias: true);

        $this->assertEquals('firstname', $schema->firstname);
        $this->assertEquals('lastname', $schema->lastname);
        $this->assertEquals(21, $schema->age);
        $this->assertEquals(false, $schema->isActive);
    }

    public function testBuildObject(): void
    {
        $schema = new GetUserSchema(
            'firstname',
            'lastname',
            21,
            false
        );

        $object = $schema->buildObject(User::class);

        $this->assertEquals('firstname', $object->firstname);
        $this->assertEquals('lastname', $object->lastname);
        $this->assertEquals(false, $object->getActive());
        $this->assertEquals(21, (new \DateTime('now', new \DateTimeZone('UTC')))->diff($object->birthday)->y);
    }

    public function testBuildObjectByAlias(): void
    {
        $schema = new GetUserSchema(
            'firstname',
            'lastname',
            21,
            false
        );

        $object = $schema->buildObject(ShortUser::class, byAlias: true);

        $this->assertEquals('firstname', $object->fname);
        $this->assertEquals('lastname', $object->lname);
        $this->assertEquals(false, $object->getActive());
        $this->assertEquals(21, (new \DateTime('now', new \DateTimeZone('UTC')))->diff($object->birthday)->y);
    }

    public function testUpdateObject(): void
    {
        $object = new User();
        $object->firstname = 'firstname';
        $object->lastname = 'lastname';
        $object->setActive(false);
        $object->birthday = new \DateTime('-21 years', new \DateTimeZone('UTC'));

        $schema = new GetUserSchema(
            'testFname',
            'testLname',
            22,
            true
        );

        /** @var User $object */
        $schema->updateObject($object);

        $this->assertEquals('testFname', $object->firstname);
        $this->assertEquals('testLname', $object->lastname);
        $this->assertEquals(true, $object->getActive());
        $this->assertEquals(22, (new \DateTime('now', new \DateTimeZone('UTC')))->diff($object->birthday)->y);
    }

    public function testToString(): void
    {
        $this->expectNotToPerformAssertions();

        $schema = new GetUserSchema(
            'testFname',
            'testLname',
            22,
            true
        );

        // echo "\n\n" . $schema . "\n\n";
    }

    public function testUpdate(): void
    {
        $original = new GetUserSchema(
            'testFname',
            'testLname',
            22,
            true
        );

        $updated = $original->update(['age' => 21,'isActive' => false]);

        $this->assertTrue($updated !== $original);
        $this->assertEquals(22, $original->age);
        $this->assertEquals(true, $original->isActive);
        $this->assertEquals(21, $updated->age);
        $this->assertEquals(false, $updated->isActive);
    }

    public function testUpdateNoUpdates(): void
    {
        $original = new OrderSchema(1, new \DateTimeImmutable('now'), 2, 3);

        $copy = $original->update();

        $this->assertTrue($original !== $copy);
        $this->assertTrue($original  == $copy);
        $this->assertTrue($original->createdAt == $copy->createdAt);
    }

    public function testUpdateWrongOrder(): void
    {
        $original = new GetUserSchema(
            'testFname',
            'testLname',
            22,
            true
        );

        $updated = $original->update(['isActive' => false, 'age' => 21]);

        $this->assertTrue($updated !== $original);
        $this->assertEquals(22, $original->age);
        $this->assertEquals(true, $original->isActive);
        $this->assertEquals(21, $updated->age);
        $this->assertEquals(false, $updated->isActive);
    }

    public function testUpdateWrongType(): void
    {
        $original = new GetUserSchema(
            'testFname',
            'testLname',
            22,
            true
        );

        $this->expectException(\TypeError::class);

        $updated = $original->update(['isActive' => 'test', 'age' => 'test']);
    }

    public function testUpdateByAlias(): void
    {
        $original = new GetUserSchema(
            'testFname',
            'testLname',
            22,
            true
        );

        $updated = $original->update(
            ['lname' => 'testL', 'fname' => 'testF'],
            true
        );

        $this->assertTrue($updated !== $original);
        $this->assertEquals('testFname', $original->firstname);
        $this->assertEquals('testLname', $original->lastname);
        $this->assertEquals('testF', $updated->firstname);
        $this->assertEquals('testL', $updated->lastname);
    }

    public function testCopy(): void
    {
        $original = new OrderSchema(1, new \DateTimeImmutable('now'), 2, 3);

        $copy = $original->copy();

        $this->assertTrue($original !== $copy);
        $this->assertTrue($original  == $copy);
        $this->assertTrue($original->createdAt == $copy->createdAt);
        $this->assertTrue($original->createdAt !== $copy->createdAt);
    }

    public function testValidate(): void
    {
        $correct = new Customer(18, 'ACTIVE', 1);
        $this->expectException(SchemaException::class);
        $incorrect = new Customer(17, 'test', 10);
        $incorrect->validate(true);
    }

    public function testValidateFromArray(): void
    {
        $correct = Customer::fromArray(
            [
                'age' => 18,
                'status' => 'BLOCKED',
                'user_id' => 1
            ],
            true
        );

        $this->expectException(SchemaException::class);

        $incorrect = Customer::fromArray(
            [
                'age' => 18,
                'status' => 'test',
                'user_id' => 1
            ],
            true
        );
    }

    public function testParse(): void
    {
        $json = '{
            "dateFrom": "2024-01-01T00:00:00",
            "dateTo": "2024-12-12T23:59:59",
            "status": "a",
            "ids": [1, 2, 3],
            "strict": true,
            "tags": {
                "foo": {
                    "name": "foo"
                },
                "eggs": {
                    "name": "eggs"
                }
            }
        }';

        $filter = FilterSchema::fromJSON($json);

        $this->assertEquals(
            (new \DateTimeImmutable('2024-01-01T00:00:00'))->getTimestamp(),
            $filter->dateFrom->getTimestamp()
        );
        $this->assertEquals(
            (new \DateTimeImmutable('2024-12-12T23:59:59'))->getTimestamp(),
            $filter->dateTo->getTimestamp()
        );
        $this->assertEquals(StatusEnum::ACTIVE, $filter->status);
        $this->assertCount(3, $filter->ids);
        $this->assertTrue($filter->strict);
        $this->assertCount(2, $filter->tags);
        $this->assertEquals('foo', $filter->tags['foo']->name);
        $this->assertEquals('eggs', $filter->tags['eggs']->name);
    }

    public function testDump(): void
    {
        $now = new \DateTime('now');

        $filter = new FilterSchema(
            ids: [1, 2, 3],
            strict: true,
            tags: [
                new Tag('tag1'),
                new Tag('tag2')
            ],
            dateTo: $now,
            dateFrom: \DateTimeImmutable::createFromMutable($now),
            status: StatusEnum::BANNED
        );

        $json = $filter->toJSON();
        $raw = json_decode($json, true);

        $this->assertEquals(
            [
                'ids' => [1, 2, 3],
                'strict' => true,
                'tags' => [
                    ['name' => 'tag1', 'icon' => null],
                    ['name' => 'tag2', 'icon' => null]
                ],
                'dateTo' => $now->format('Y-m-d\TH:i:s'),
                'dateFrom' => $now->format('Y-m-d\TH:i:s'),
                'status' => 'b'
            ],
            $raw
        );
    }

    public function testFromNumericArray(): void
    {
        $dateTo = new \DateTime('now');
        $dateFrom = new \DateTimeImmutable('-1 day');

        $array = [
            [1,2,3],
            false,
            [['name1', 'icon1'], ['name1', 'icon1']],
            $dateTo->format('Y-m-d\TH:i:s'),
            $dateFrom->format('Y-m-d\TH:i:s'),
            StatusEnum::DELETED->value
        ];

        $schema = FilterSchema::fromArray(
            raw: $array,
            parse: true,
            dateTimeFormat: 'Y-m-d\TH:i:s'
        );

        $this->assertEquals([1,2,3], $schema->ids);
        $this->assertEquals(false, $schema->strict);
        $this->assertCount(2, $schema->tags);
        $this->assertEquals($dateTo->getTimestamp(), $schema->dateTo->getTimestamp());
        $this->assertEquals($dateFrom->getTimestamp(), $schema->dateFrom->getTimestamp());
    }

    public function testFromNumericArrayShorterThanFeilds(): void
    {
        $dateTo = new \DateTime('now');
        $dateFrom = new \DateTimeImmutable('-1 day');

        // status is mising, should use default value
        $array = [
            [1,2,3],
            false,
            [['name1', 'icon1'], ['name1', 'icon1']],
            $dateTo->format('Y-m-d\TH:i:s'),
            $dateFrom->format('Y-m-d\TH:i:s')
        ];

        $schema = FilterSchema::fromArray(
            raw: $array,
            parse: true,
            dateTimeFormat: 'Y-m-d\TH:i:s'
        );

        $this->assertEquals([1,2,3], $schema->ids);
        $this->assertEquals(false, $schema->strict);
        $this->assertCount(2, $schema->tags);
        $this->assertEquals($dateTo->getTimestamp(), $schema->dateTo->getTimestamp());
        $this->assertEquals($dateFrom->getTimestamp(), $schema->dateFrom->getTimestamp());
        $this->assertNull($schema->status);
    }

    public function testFromNumericArrayBiggerThanFields(): void
    {
        $dateTo = new \DateTime('now');
        $dateFrom = new \DateTimeImmutable('-1 day');

        // some extra fields
        $array = [
            [1,2,3],
            false,
            [['name1', 'icon1'], ['name1', 'icon1']],
            $dateTo->format('Y-m-d\TH:i:s'),
            $dateFrom->format('Y-m-d\TH:i:s'),
            StatusEnum::DELETED->value,
            1,
            2,
            3
        ];

        $schema = FilterSchema::fromArray(
            raw: $array,
            parse: true,
            dateTimeFormat: 'Y-m-d\TH:i:s'
        );

        $this->assertEquals([1,2,3], $schema->ids);
        $this->assertEquals(false, $schema->strict);
        $this->assertCount(2, $schema->tags);
        $this->assertEquals($dateTo->getTimestamp(), $schema->dateTo->getTimestamp());
        $this->assertEquals($dateFrom->getTimestamp(), $schema->dateFrom->getTimestamp());
    }

    public function testFromArrayMultiple(): void
    {
        $raw = [
            [ 'name1', 100.1, [ ['name1tag1', null], ['name1tag2', 'name1icon2'], ], 'description1' ],
            [ 'name2', 100.2, [ ['name2tag1', null], ['name2tag2', 'name2icon2'], ], 'description2' ],
        ];

        $parsed = Product::fromArrayMultiple(
            rows: $raw,
            byAlias: true,
            reduce: true,
            dateTimeFormat: 'Y-m-d\TH:i:s'
        );

        $this->assertCount(0, $raw);
        $this->assertCount(2, $parsed);
        $pNumber = 0;
        foreach ($parsed as $row) {
            $pNumber++;
            $this->assertInstanceOf(Product::class, $row);
            $this->assertEquals("name$pNumber", $row->name);
            $this->assertEquals("description$pNumber", $row->description);
            $this->assertEquals(100 + $pNumber/10, $row->price);
            $this->assertCount(2, $row->tags);
        }
    }

    public function testFromArrayMultipleExcludeByValidation(): void
    {
        $raw = [
            [ 'status' => 'ACTIVE', 'age' => 19, 'user_id' => 42 ],
            [ 'status' => 'foo',    'age' => 10, 'user_id' => 42 ],
        ];

        $parsed = Customer::fromArrayMultiple(
            rows: $raw,
            byAlias: true,
            validate: 'exclude',
            reduce: true,
            dateTimeFormat: 'Y-m-d\TH:i:s'
        );

        $this->assertCount(0, $raw);
        $this->assertCount(1, $parsed);
        $this->assertEquals('ACTIVE', end($parsed)->status);
    }

    public function testFromJSONMultiple(): void
    {
        $source = [
            [
                "ids" => [1,2,3],
                "strict" => true,
                "tags" => [
                    ["name" => "row1name1", "icon" => null],
                    ["name" => "row1name2", "icon" => "row1icon2"]
                ],
                "dateTo" => "2024-12-31T23:59:59",
                "dateFrom" => "2024-01-01T00:00:00",
                "status" => "DELETED"
            ],
            [
                "ids" => [4,5,6],
                "strict" => true,
                "tags" => [
                    ["name" => "row2name1", "icon" => null],
                    ["name" => "row2name2", "icon" => "row2icon2"]
                ],
                "dateTo" => "2025-12-31T23:59:59",
                "dateFrom" => "2025-01-01T00:00:00"
            ]
        ];
        $json = json_encode($source);

        $parsed = FilterSchema::fromJSONMultiple($json, validate: 'throw');

        $this->assertCount(2, $parsed);
    }

    public function testFromJSONMultipleJSONCompact(): void
    {
        $source = [
            [
                [1,2,3],
                true,
                [
                    ["row1name1", null],
                    ["row1name2", "row1icon2"]
                ],
                "2024-12-31T23:59:59",
                "2024-01-01T00:00:00",
                "DELETED"
            ],
            [
                [4,5,6],
                true,
                [
                    ["row2name1", null],
                    ["row2name2", "row2icon2"]
                ],
                "2025-12-31T23:59:59",
                "2025-01-01T00:00:00",
                null
            ]
        ];
        $json = json_encode($source);

        $parsed = FilterSchema::fromJSONMultiple($json, validate: 'throw');

        $this->assertCount(2, $parsed);
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }
}
