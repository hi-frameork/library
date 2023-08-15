<?php

namespace Tests\Suites\Queue;

use Exception;
use Library\Queue\Manager;
use Library\Queue\NotFoundException;

class ConsumerTest extends TestCase
{
    // 测试未传入消费者名称时，是否会抛出异常
    public function testConsumeWhenEmptyThrowNotFoundException(): void
    {
        // $manager = new Manager([], [
        //     __DIR__ . '/Stubs',
        // ]);

        // $this->expectException(NotFoundException::class);
        // $this->expectExceptionMessage("Class or alias '' not found");

        // $manager->consume('');
    }

    // 测试未传入配置连接时，是否会抛出异常
    public function testConsumeWhenEmptyConfigThrowException(): void
    {
        // $manager = new Manager([], [
        //     __DIR__ . '/Stubs',
        // ]);

        // $this->expectException(Exception::class);
        // $this->expectExceptionMessage("Queue config kafka.default not found");

        // $manager->consume('user_online_status', false);
    }

    // 测试传入正确的配置连接时，是否会正常消费
    public function testConsumeWhenCorrectConfig(): void
    {
        // $this->manager->consume('user_online_status', false);
        // // 值来自 Tests\Suites\Queue\ProducerTest::testProducerWithObject 方法
        // $this->expectOutputString('{"user_id":1,"online_status":1}');
    }
}
