<?php

namespace Tests\Suites\Queue\Parser;

use Library\Attribute\Queue\Consumer;
use Library\Queue\Parser;
use PHPUnit\Framework\TestCase;

/**
 * 消费者注解测试
 */
class ConsumerAttributeTest extends TestCase
{
    // 测试消费者类没有注解情况 -  将不会被解析
    public function testParseWithConsumerNoAttribute()
    {
        $classes = [
            Stubs\NoAttributeConsumer001::class,
            Stubs\NoAttributeConsumer002::class,
        ];
        $parser = new Parser($classes, Consumer::class);
        $this->assertSame(
            [
                'classes' => [],
                'aliases' => [],
            ],
            $parser->getParsed()
        );
    }

    // 测试消费者类加载与解析
    // 别名/别名组、类名以及注解测试
    public function testParseWithConsumerHasAttribute()
    {
        // 解析测试
        $classes = [
            Stubs\AttributeConsumer001::class,
            Stubs\AttributeConsumer002::class,
        ];
        $parser = new Parser($classes, Consumer::class);
        $parsed = $parser->getParsed();
        $this->assertNotEmpty($parsed['classes']);
        // 别名被成功解析
        $this->assertArrayHasKey('consumer001', $parsed['aliases']);
        $this->assertArrayHasKey('consumer002', $parsed['aliases']);
        // 所有别名都是数组形式组织
        $this->assertIsArray($parsed['aliases']['consumer001']);
        $this->assertIsArray($parsed['aliases']['consumer002']);
        // 消费者类名被成功解析
        $this->assertArrayHasKey(Stubs\AttributeConsumer001::class, $parsed['classes']);
        $this->assertArrayHasKey(Stubs\AttributeConsumer002::class, $parsed['classes']);

        // 类结构测试
        $class = $parser->get(Stubs\AttributeConsumer001::class)[0];
        $this->assertArrayHasKey('class', $class);
        $this->assertArrayHasKey('attribute', $class);
        $this->assertSame(Stubs\AttributeConsumer001::class, $class['class']);
        $this->assertInstanceOf(Consumer::class, $class['attribute']);

        // 注解结构测试
        $aliases = $parser->get('consumer001');
        $this->assertIsArray($aliases);
        $this->assertCount(1, $aliases);
        /** @var Consumer $attribute */
        $attribute = $aliases[0]['attribute'];
        $this->assertSame('consumer001', $attribute->alias);
    }

    // 测试消费者类加载与解析 - 消费者类注解混合(部分有注解，部分没有注解)
    // 测试目的：消费者类注解混合(部分有注解，部分没有注解)的情况下，是否能够正常解析
    public function testParseWithConsumerMix()
    {
        // 解析测试
        $classes = [
            Stubs\AttributeConsumer001::class,
            Stubs\AttributeConsumer002::class,
            Stubs\AttributeConsumer003::class,
            Stubs\AttributeConsumer004::class,
            Stubs\NoAttributeConsumer001::class,
            Stubs\NoAttributeConsumer002::class,
        ];
        $parser = new Parser($classes, Consumer::class);
        $parsed = $parser->getParsed();

        // 四个消费者类被成功解析
        $this->assertCount(4, $parsed['classes']);
        // 三个消费者类别名被成功解析
        $this->assertCount(3, $parsed['aliases']);

        // 消费者别名组
        $aliases = $parser->get('consumer-alias-group');
        $this->assertCount(2, $aliases);
        $this->assertSame(Stubs\AttributeConsumer003::class, $aliases[0]['class']);
        $this->assertSame(Stubs\AttributeConsumer004::class, $aliases[1]['class']);
    }
}
