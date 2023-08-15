<?php

namespace Tests\Suites\Queue\Parser;

use Library\Attribute\Queue\Producer;
use Library\Queue\Parser;
use PHPUnit\Framework\TestCase;

/**
 * 生产者属性解析测试
 */
class ProducerAttributeTest extends TestCase
{
    // 测试生产者类没有注解情况 -  将不会被解析
    public function testParseWithProducerNoAttribute()
    {
        $classes = [
            Stubs\NoAttributeProducer001::class,
            Stubs\NoAttributeProducer002::class,
        ];
        $parser = new Parser($classes, Producer::class);
        $this->assertSame(
            [
                'classes' => [],
                'aliases' => [],
            ],
            $parser->getParsed()
        );
    }

    // 测试生产者类加载与解析
    // 别名/别名组、类名以及注解测试
    public function testParseWithProducerHasAttribute()
    {
        // 解析测试
        $classes = [
            Stubs\AttributeProducer001::class,
            Stubs\AttributeProducer002::class,
        ];
        $parser = new Parser($classes, Producer::class);
        $parsed = $parser->getParsed();
        $this->assertNotEmpty($parsed['classes']);
        // 别名被成功解析
        $this->assertArrayHasKey('producer001', $parsed['aliases']);
        $this->assertArrayHasKey('producer002', $parsed['aliases']);
        // 所有别名都是数组形式组织
        $this->assertIsArray($parsed['aliases']['producer001']);
        $this->assertIsArray($parsed['aliases']['producer002']);
        // 生产者类名被成功解析
        $this->assertArrayHasKey(Stubs\AttributeProducer001::class, $parsed['classes']);
        $this->assertArrayHasKey(Stubs\AttributeProducer002::class, $parsed['classes']);
        // 类结构测试
        $class = $parser->get(Stubs\AttributeProducer001::class)[0];
        $this->assertArrayHasKey('class', $class);
        $this->assertArrayHasKey('attribute', $class);
        $this->assertSame(Stubs\AttributeProducer001::class, $class['class']);
        $this->assertInstanceOf(Producer::class, $class['attribute']);
        // 注解结构测试
        /** @var Producer $attribute */
        $attribute = $parser->get('producer001')[0]['attribute'];
        // $attribute = $parsed['aliases']['producer001'][0]['attribute'];
        $this->assertSame('producer001', $attribute->alias);
    }

    // 测试生产者类加载与解析 - 生产者类注解混合(部分有注解，部分没有注解)
    // 测试目的：生产者类注解混合(部分有注解，部分没有注解)的情况下，是否能够正常解析
    public function testParseWithProducerMix()
    {
        // 解析测试
        $classes = [
            Stubs\AttributeProducer001::class,
            Stubs\AttributeProducer002::class,
            Stubs\AttributeProducer003::class,
            Stubs\AttributeProducer004::class,
            Stubs\NoAttributeProducer001::class,
            Stubs\NoAttributeProducer002::class,
        ];
        $parser = new Parser($classes, Producer::class);
        $parsed = $parser->getParsed();

        // 四个消费者类被成功解析
        $this->assertCount(4, $parsed['classes']);
        // 三个消费者类别名被成功解析
        $this->assertCount(3, $parsed['aliases']);

        // 消费者别名组
        $aliases = $parser->get('producer-group');
        $this->assertCount(2, $aliases);
        $this->assertSame(Stubs\AttributeProducer003::class, $aliases[0]['class']);
        $this->assertSame(Stubs\AttributeProducer004::class, $aliases[1]['class']);
    }
}
