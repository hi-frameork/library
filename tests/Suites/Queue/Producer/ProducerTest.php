<?php

namespace Tests\Suites\Queue\Producer;

use Library\Queue\Manager;
use longlang\phpkafka\Protocol\CreateTopics\CreatableTopic;
use longlang\phpkafka\Protocol\CreateTopics\CreateTopicsRequest;
use Tests\Suites\Queue\Producer\Stubs\SimpleProducer;
use Tests\Suites\Queue\Producer\Stubs\Topic;
use Tests\Suites\Queue\TestCase;

class ProducerTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->deleteTopic([
            Topic::LibraryProducerCreateTopicTest->value,
            Topic::AttributeProducer100->value,
            Topic::AttributeProducer101->value,
            Topic::AttributeProducerGroup100->value,
            Topic::AttributeProducerGroup101->value,
        ]);
    }

    //  测试创建 topic
    public function testCreateTopic()
    {
        $client = $this->createKafkaClient();
        $client->connect();

        $request = new CreateTopicsRequest();
        $request->setTopics([
            (new CreatableTopic())
                ->setName(Topic::LibraryProducerCreateTopicTest->value)
                ->setNumPartitions(3)
                ->setReplicationFactor(-1)
        ]);
        $request->setTimeoutMs(10000);
        // $request->setValidateOnly(true);
        $correlationId = $client->send($request);
        $client->close();

        $this->assertGreaterThan(0, $correlationId);
    }

    // 测试生产者投递消息
    public function testProduceWithProducerObject()
    {
        $manager = new Manager([
            'kafka-default' => config('queue.kafka-default'),
        ], [
            __DIR__ . '/Stubs',
        ]);

        // 投递 50 个消息，观察分区情况
        for ($i = 0; $i < 50; $i++) {
            $manager->produce(new SimpleProducer(['time' => time()]));
        }
    }

    // 测试生产者投递消息 - 通过生产者别名投递单条
    public function testProducerWithProducerAliasForSingle()
    {
        $this->manager->produce('producer-test-100', [
            'user_id'       => 1,
            'online_status' => 1,
        ]);
        $this->manager->produce('producer-test-101', [
            'user_id'       => 1,
            'online_status' => 1,
        ]);
    }

    // 测试生产者投递消息 - 通过生产者别名投递单条
    public function testProducerWithProducerAliasForGroup()
    {
        $this->manager->produce('producer-test-group-000', [
            'user_id' => 2,
            'time'    => time(),
        ]);
    }
}
