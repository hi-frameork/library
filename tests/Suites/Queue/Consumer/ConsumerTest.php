<?php

namespace Tests\Suites\Queue\Consumer;

use Library\Coroutine;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use stdClass;
use Tests\Suites\Queue\Consumer\Stubs\MultiPartitionMultiConsumerProducer;
use Tests\Suites\Queue\Consumer\Stubs\MultiPartitionSingleConsumer;
use Tests\Suites\Queue\Consumer\Stubs\MultiPartitionSingleConsumerProducer;
use Tests\Suites\Queue\Consumer\Stubs\SinglePartitionMultiConsumerProducer;
use Tests\Suites\Queue\Consumer\Stubs\SinglePartitionSingleConsumer;
use Tests\Suites\Queue\Consumer\Stubs\SinglePartitionSingleConsumerProducer;
use Tests\Suites\Queue\Consumer\Stubs\Topic;
use Tests\Suites\Queue\TestCase;

/**
 * 消费者测试
 */
class ConsumerTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->deleteTopic([
            Topic::SinglePartitionSingleConsumer->value,
            Topic::MultiPartitionSignleConsumer->value,
            Topic::SinglePartitionMultiConsumer->value,
            Topic::MultiPartitionMultiConsumer->value,
        ]);
    }

    // 测试单分区单消费者情况
    public function testSinglePartitionSingleConsumer()
    {
        // $this->markTestSkipped();

        $this->createTopic(Topic::SinglePartitionSingleConsumer->value, 1, 1);

        $data = [
            'method' => __METHOD__,
            'time'   => time(),
        ];
        $count = 2;

        for ($i = 0; $i < $count; $i++) {
            $this->manager->produce(new SinglePartitionSingleConsumerProducer($data));
        }
        for ($i = 0; $i < $count; $i++) {
            $this->manager->consume(SinglePartitionSingleConsumer::class, false);
        }

        $contents = implode('', array_fill(0, $count, json_encode($data)));
        $this->expectOutputString($contents);
    }

    // 测试多分区单消费者情况
    public function testMultiPartitionSingleConsumer()
    {
        // $this->markTestSkipped();

        $this->createTopic(Topic::MultiPartitionSignleConsumer->value, 3, 1);

        $data = [
            'method' => __METHOD__,
            'time'   => time(),
        ];
        $count = 6;

        for ($i = 0; $i < $count; $i++) {
            $this->manager->produce(new MultiPartitionSingleConsumerProducer($data));
        }
        for ($i = 0; $i < $count; $i++) {
            $this->manager->consume(MultiPartitionSingleConsumer::class, false);
        }

        $contents = implode('', array_fill(0, $count, json_encode($data)));
        $this->expectOutputString($contents);
    }

    // 测试单分区多消费者情况
    public function testSinglePartitionMultiConsumer()
    {
        // $this->markTestSkipped();
        set_exception_handler(fn () => print_r(func_get_args()));

        $this->createTopic(Topic::SinglePartitionMultiConsumer->value, 1, 3);

        $object            = new stdClass();
        $object->count     = 0;
        $object->consumers = [];
        $object->contents  = [];

        // 批量创建 2 个消费者
        for ($i = 0; $i < 1; $i++) {
            Coroutine::create(function () use ($object) {
                $config   = $this->createConsumerConfig(Topic::SinglePartitionMultiConsumer->value);
                $consumer = new Consumer(
                    $config,
                    function (ConsumeMessage $message) use ($object) {
                        // echo 'Consumer 1: ', $message->getValue(), PHP_EOL;
                        $object->contents[] = $message->getValue();
                        $object->count++;
                    }
                );
                $object->consumers[] = $consumer;
                $consumer->start();
            });
        }

        $data = [
            'method' => __METHOD__,
            'time'   => time(),
        ];
        $count = 6;
        for ($i = 0; $i < $count; $i++) {
            $this->manager->produce(new SinglePartitionMultiConsumerProducer($data));
        }

        // 等待消费完成
        while (true) {
            if ($object->count >= $count) {
                break;
            }
            Coroutine::sleep(0.05);
        }

        // 关闭消费者
        /** @var Consumer $consumer */
        foreach ($object->consumers as $consumer) {
            $consumer->stop();
            $consumer->close();
        }

        foreach ($object->contents as $item) {
            $this->assertSame($item, json_encode($data));
        }
    }

    // 测试多分区多消费者情况
    public function testMultiPartitionMultiConsumer()
    {
        // $this->markTestSkipped();

        $this->createTopic(Topic::MultiPartitionMultiConsumer->value, 3, 1);

        $object            = new stdClass();
        $object->count     = 0;
        $object->consumers = [];
        $object->contents  = [];

        // 批量创建 3 个消费者
        for ($i = 0; $i < 3; $i++) {
            Coroutine::create(function () use ($object, $i) {
                $config   = $this->createConsumerConfig(Topic::MultiPartitionMultiConsumer->value);
                $consumer = new Consumer(
                    $config,
                    function (ConsumeMessage $message) use ($object, $i) {
                        // echo 'Consumer : ', $i, ' ' , $message->getValue(), PHP_EOL;
                        $object->contents[] = $message->getValue();
                        $object->count++;
                    }
                );
                $object->consumers[] = $consumer;
                $consumer->start();
            });
        }

        $data = [
            'method' => __METHOD__,
            'time'   => time(),
        ];
        $count = 6;
        for ($i = 0; $i < $count; $i++) {
            $this->manager->produce(new MultiPartitionMultiConsumerProducer($data));
        }

        // 等待消费完成
        while (true) {
            if ($object->count >= $count) {
                break;
            }
            Coroutine::sleep(0.05);
        }

        // 关闭消费者
        /** @var Consumer $consumer */
        foreach ($object->consumers as $consumer) {
            try {
                $consumer->stop();
                $consumer->close();
            } catch (\Throwable) {
            }
        }

        foreach ($object->contents as $item) {
            $this->assertSame($item, json_encode($data));
        }
    }
}
