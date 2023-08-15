<?php

namespace Tests\Suites\Queue\Config;

use Library\Queue\Config;
use Library\Queue\KafkaItem;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * 队列配置测试
 *
 * @coversDefaultClass \Library\Queue\Config
 */
class ConfigTest extends TestCase
{
    // 测试正常组解析
    public function testConstructNormal()
    {
        // 配置为空，返回空数组
        $this->assertCount(0, (new Config([]))->getList());

        // 配置不为空，配置想数量为 1
        $data = [
            'kafka-default' => [
                'bootstrapServers' => 'kafka:29092',
                'brokers'          => 'kafka:29092',
            ],
        ];
        $this->assertCount(1, (new Config($data))->getList());

        // 配置不为空，配置想数量为 2
        $data = [
            'kafka-default' => [
                'bootstrapServers' => 'kafka:29092',
                'brokers'          => 'kafka:29092',
            ],
            'kafka-test' => [
                'bootstrapServers' => 'kafka:29092',
                'brokers'          => 'kafka:29092',
            ],
        ];
        $this->assertCount(2, (new Config($data))->getList());
    }

    // 测试获取配置项
    public function testGetNormal()
    {
        $config = new Config([
            'kafka-default' => [
                'bootstrapServers' => 'kafka:29092',
                'brokers'          => 'kafka:29092',
            ],
        ]);
        $this->assertInstanceOf(kafkaItem::class, $config->get('kafka-default'));
        $this->assertSame('kafka:29092', $config->get('kafka-default')->bootstrapServers);
        $this->assertSame('kafka:29092', $config->get('kafka-default')->brokers);
    }

    // 测试获取不存在的配置项 - 抛出异常
    public function testGetWhenConfigNotFound()
    {
        $config = new Config([]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Queue config kafka-default not found');
        $config->get('kafka-default');
    }
}
