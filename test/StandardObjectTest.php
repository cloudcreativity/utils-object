<?php

/**
 * Copyright 2017 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace CloudCreativity\Utils\Object;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

/**
 * Class StandardObjectTest
 *
 * @package CloudCreativity\Utils\Object
 */
class StandardObjectTest extends TestCase
{

    /**
     * @var object
     */
    private $proxy;

    /**
     * @var StandardObject
     */
    private $object;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->proxy = (object) [
            'type' => 'posts',
            'id' => 1,
            'attributes' => (object) [
                'title' => 'Hello World',
                'content' => 'My first post',
            ],
        ];

        $this->object = new StandardObject($this->proxy);
    }

    public function testClone()
    {
        $copy = clone $this->object;
        $copy->set('foo', 'bar');

        $this->assertNull($this->object->get('foo'));
    }

    public function testCloneIsDeep()
    {
        $copy = clone $this->object;
        $copy->get('attributes')->set('foo', 'bar');

        $this->assertNull($this->object->get('attributes')->get('foo'));
    }

    public function testCopy()
    {
        $this->object->copy()->get('attributes')->set('foo', 'bar');
        $this->assertNull($this->object->get('foo'));
    }

    public function testMagicGet()
    {
        $this->assertSame($this->proxy->type, $this->object->type);
        $this->assertSame($this->proxy->id, $this->object->id);
        $this->assertSame($this->proxy->attributes, $this->object->attributes);
    }

    public function testMagicGetInvalidKey()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->object->foo;
    }

    public function testNullValue()
    {
        $this->object->foo = null;
        $this->assertNull($this->object->foo);
    }

    public function testIsSet()
    {
        $this->assertTrue(isset($this->object->type));
        $this->assertFalse(isset($this->object->foo));
    }

    public function testUnset()
    {
        unset($this->object->type);
        $this->assertFalse(isset($this->object->type));
    }

    public function testIterator()
    {
        $expected = (array) $this->proxy;
        $expected['attributes'] = new StandardObject($expected['attributes']);
        $this->assertEquals($expected, iterator_to_array($this->object));
    }

    public function testCount()
    {
        $this->assertEquals(count((array) $this->proxy), count($this->object));
    }

    public function testGet()
    {
        $this->assertSame('posts', $this->object->get('type'));
        $this->assertNull($this->object->get('foo'));
        $this->assertTrue($this->object->get('foo', true));
    }

    public function testGetObject()
    {
        $expected = new StandardObject($this->proxy->attributes);

        $this->assertEquals($expected, $this->object->get('attributes'));
        $this->assertEquals('Hello World', $this->object->get('attributes')->get('title'));
    }

    public function testGetProperties()
    {
        $expected = [
            'type' => 'posts',
            'foo' => null,
            'attributes' => $this->proxy->attributes,
        ];

        $this->assertSame($expected, $this->object->getProperties('type', 'foo', 'attributes'));
        $this->assertSame($expected, $this->object->getProperties(['type', 'foo', 'attributes']));
    }

    public function testGetPropertiesNone()
    {
        $this->assertEquals([], $this->object->getProperties([]));
        $this->assertEquals([], $this->object->getProperties());
    }

    public function testGetMany()
    {
        $expected = [
            'type' => 'posts',
            'attributes' => $this->proxy->attributes,
        ];

        $this->assertSame($expected, $this->object->getMany('type', 'foo', 'attributes'));
        $this->assertSame($expected, $this->object->getMany(['type', 'foo', 'attributes']));
    }

    public function testGetManyNone()
    {
        $this->assertEquals([], $this->object->getMany([]));
        $this->assertEquals([], $this->object->getMany());
    }

    public function testSet()
    {
        $this->object->set('type', 'comments')->set('foo', 'bar');

        $this->assertEquals('comments', $this->object->get('type'));
        $this->assertEquals('bar', $this->object->get('foo'));
    }

    public function testSetProperties()
    {
        $actual = $this->object->setProperties([
            'type' => 'comments',
            'foo' => 'bar',
        ]);

        $this->assertSame($this->object, $actual);
        $this->assertEquals('comments', $this->object->get('type'));
        $this->assertEquals('bar', $this->object->get('foo'));
    }

    public function testAdd()
    {
        $this->object->add('type', 'comments')->add('foo', 'bar');

        $this->assertEquals('posts', $this->object->get('type'));
        $this->assertEquals('bar', $this->object->get('foo'));
    }

    public function testAddProperties()
    {
        $actual = $this->object->addProperties([
            'type' => 'comments',
            'foo' => 'bar',
        ]);

        $this->assertSame($this->object, $actual);
        $this->assertEquals('posts', $this->object->get('type'));
        $this->assertEquals('bar', $this->object->get('foo'));
    }

    public function testHas()
    {
        $this->assertTrue($this->object->has('type', 'id'));
        $this->assertTrue($this->object->has(['type', 'id']));
        $this->assertFalse($this->object->has('type', 'id', 'foo'));
    }

    public function testHasAny()
    {
        $this->assertTrue($this->object->hasAny('foo', 'bar', 'type'));
        $this->assertTrue($this->object->hasAny(['foo', 'bar', 'type']));
        $this->assertFalse($this->object->hasAny('foo', 'bar'));
    }

    public function testRemove()
    {
        $actual = $this->object->remove('type', 'id');

        $this->assertSame($this->object, $actual);
        $this->assertFalse($this->object->hasAny('type', 'id'));
    }

    public function testReduce()
    {
        $this->assertSame($this->object, $this->object->reduce('type', 'id'));
        $this->assertSame(['type' => 'posts', 'id' => 1], $this->object->toArray());
    }

    public function testKeys()
    {
        $this->object->set('foo', null);
        $expected = ['type', 'id', 'attributes', 'foo'];
        $this->assertEquals($expected, $this->object->keys());
    }

    public function testRename()
    {
        $this->assertSame($this->object, $this->object->rename('type', 'resource_type'));
        $this->assertFalse($this->object->has('type'));
        $this->assertSame('posts', $this->object->get('resource_type'));
    }

    public function testRenameKeys()
    {
        $actual = $this->object->renameKeys([
            'type' => 'resource_type',
            'foo' => 'bar',
            'id' => 'resource_id',
        ]);

        $this->assertSame($this->object, $actual);
        $this->assertFalse($this->object->has('type', 'id'));
        $this->assertSame('posts', $this->object->get('resource_type'));
        $this->assertSame(1, $this->object->get('resource_id'));
    }

    public function testTransform()
    {
        $actual = $this->object->transform(function ($value) {
            return is_int($value) ? 999 : strtoupper($value);
        }, 'type', 'foo', 'id');

        $this->assertSame($this->object, $actual);
        $this->assertSame(999, $this->object->get('id'));
        $this->assertSame('POSTS', $this->object->get('type'));
    }

    public function testTransformIssue1()
    {
        $object = new StandardObject((object) [
            'foo' => '1',
            'bar' => '2',
        ]);

        $object->transform('intval', 'foo', 'bar');
        $this->assertSame(1, $object->get('foo'));
        $this->assertSame(2, $object->get('bar'));
    }

    public function testTransformKeys()
    {
        $actual = $this->object->transformKeys(function ($key) {
            return strtoupper($key);
        });

        $this->assertSame($this->object, $actual);
        $this->assertEquals(['TYPE', 'ID', 'ATTRIBUTES'], $this->object->keys());
    }

    public function testToArray()
    {
        $expected = (array) $this->proxy;
        $expected['attributes'] = (array) $this->proxy->attributes;

        $this->assertEquals($expected, $this->object->toArray());
    }

    public function testToStdClass()
    {
        $this->assertEquals($this->proxy, $copy = $this->object->toStdClass());
        $copy->foo = 'bar';
        $copy->attributes->foo = 'bar';
        $this->assertNotEquals($this->proxy, $copy);
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(json_encode($this->proxy), json_encode($this->object));
    }
}
