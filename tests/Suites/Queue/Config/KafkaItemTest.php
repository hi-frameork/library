<?php

namespace Tests\Suites\Queue\Config;

use Library\Queue\KafkaItem;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class KafkaItemTest extends TestCase
{
    // 测试 kafka  bootstrapservers 空数组配置解析 - 抛出异常
    public function testConstructEmptyBootstrapserverWithThrowException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Kafaka config bootstrapServers not found');
        new KafkaItem([]);
    }

    // 测试 kafka brokers 空数组配置解析 - 抛出异常
    public function testConstructEmptyBrokersWithThrowException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Kafka config brokers not found');
        new KafkaItem([
            'bootstrapServers' => 'kafka:29092'
        ]);
    }

    // 测试 kafka 配置解析 - 正常解析
    public function testConstructNormal()
    {
        $config = new KafkaItem([
            'bootstrapServers' => 'kafka:29092',
            'brokers'          => 'kafka:29092',
        ]);

        $this->assertSame('kafka:29092', $config->bootstrapServers);
        $this->assertSame('kafka:29092', $config->brokers);
    }

}
