<?php

namespace Tests\Queue;

use Library\Attribute\Queue\Consumer;
use Library\Attribute\Queue\Producer;
use Library\Queue\Parser;
use Library\System\File;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    protected array $classes = [];

    protected function setUp(): void
    {
        $this->classes = array_keys(File::scanDirectoryClass(__DIR__ . '/Stubs'));
    }

    // 测试不存砸需要解析的类
    public function testWithEmpty()
    {
        $parser = new Parser([], '');
        $this->assertEmpty($parser->getParsed()['classes']);
        $this->assertSame(
            [
                'classes' => [],
                'aliases' => [],
            ],
            $parser->getParsed()
        );
    }

    // 测试消费者类加载与解析
    public function testWithConsumerAttribute()
    {
        $parser = new Parser($this->classes, Consumer::class);
        $parsed = $parser->getParsed();
        $this->assertNotEmpty($parsed['classes']);
        $this->assertArrayHasKey('user_online_status', $parsed['aliases']);
        $this->assertArrayHasKey(Stubs\UserOnlineStatusConsumer::class, $parsed['classes']);

        // 结构测试
        $class = $parser->get(Stubs\UserOnlineStatusConsumer::class)[0];
        $this->assertArrayHasKey('class', $class);
        $this->assertArrayHasKey('attribute', $class);
        $this->assertSame(Stubs\UserOnlineStatusConsumer::class, $class['class']);
        $this->assertInstanceOf(Consumer::class, $class['attribute']);

        // 别名测试
        /** @var Consumer $attribute */
        $attribute = $parser->get('user_online_status')[0]['attribute'];
        // $attribute = $parsed['aliases']['user_online_status'][0]['attribute'];
        $this->assertSame('user_online_status', $attribute->alias);
    }

    // 测试生产者类加载与解析
    public function testWithProducerAttribute()
    {
        $parser = new Parser($this->classes, Producer::class);
        $parsed = $parser->getParsed();
        $this->assertNotEmpty($parsed['classes']);
        $this->assertArrayHasKey('user_online_status', $parsed['aliases']);
        $this->assertArrayHasKey(Stubs\UserOnlineStatusProducer::class, $parsed['classes']);

        // 结构测试
        $class = $parser->get(Stubs\UserOnlineStatusProducer::class)[0];
        // $class = $parsed['classes'][Stubs\UserOnlineStatusProducer::class][0];
        $this->assertArrayHasKey('class', $class);
        $this->assertArrayHasKey('attribute', $class);
        $this->assertSame(Stubs\UserOnlineStatusProducer::class, $class['class']);
        $this->assertInstanceOf(Producer::class, $class['attribute']);

        // 别名测试
        /** @var Consumer $attribute */
        $attribute = $parser->get('user_online_status')[0]['attribute'];
        $this->assertSame('user_online_status', $attribute->alias);
    }
}
