<?php

namespace Tests\Suites\Queue;

use Library\Queue\Manager;
use longlang\phpkafka\Client\SwooleClient;
use longlang\phpkafka\Config\CommonConfig;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Protocol\CreateTopics\CreatableTopic;
use longlang\phpkafka\Protocol\CreateTopics\CreateTopicsRequest;
use longlang\phpkafka\Protocol\DeleteTopics\DeleteTopicsRequest;
use longlang\phpkafka\Protocol\Metadata\MetadataRequest;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager([
            'kafka-default' => config('queue.kafka-default'),
        ], [
            __DIR__,
        ]);
    }

    /**
     * @return SwooleClient
     */
    protected function createKafkaClient()
    {
        // host.docker.internal:29092
        $conConfig = $this->manager->getConfig()->get('kafka-default');
        $part      = explode(':', $conConfig->bootstrapServers);

        $config = new CommonConfig();
        $config->setSendTimeout(10);
        $config->setRecvTimeout(10);
        $class = SwooleClient::class;
        /** @var SwooleClient $client */
        $client = new $class($part[0], $part[1], $config);
        $client->connect();
        // $this->assertSame([], $client->getApiKeys());
        $request = new MetadataRequest();
        /** @var MetadataResponse $response */
        $response = $client->sendRecv($request);
        $client->close();

        $nodeId = $response->getControllerId();
        foreach ($response->getBrokers() as $broker) {
            if ($broker->getNodeId() === $nodeId) {
                return new $class($broker->getHost(), $broker->getPort(), $config);
            }
        }

        throw new \RuntimeException('getControllerClient failed');
    }

    /**
     * 创建 topic
     */
    protected function createTopic($name, $partition, $replicationFactor)
    {
        $client = $this->createKafkaClient();
        $client->connect();

        $request = new CreateTopicsRequest();
        $request->setTopics([
            (new CreatableTopic())->setName($name)->setNumPartitions($partition)->setReplicationFactor($replicationFactor)
        ]);
        $request->setTimeoutMs(10000);
        // $request->setValidateOnly(true);
        $correlationId = $client->send($request);

        $this->assertGreaterThan(0, $correlationId);

        // 关闭 kafka 连接
        $client->close();

        return [$client, $correlationId];
    }

    /**
     * 测试结束， 删除 topic
     * @param array<string> $names
     */
    public function deleteTopic(array $names): void
    {
        $client = $this->createKafkaClient();
        $client->connect();

        $request = new DeleteTopicsRequest();
        $request->setTopicNames($names);
        $request->setTimeoutMs(10000);
        $correlationId = $client->send($request);
        $this->assertGreaterThan(0, $correlationId);

        $client->close();
    }

    // 创建消费者配置
    protected function createConsumerConfig(string $topic)
    {
        $config = new ConsumerConfig();
        $config->setTopic($topic);
        $config->setInterval(0.1);
        $config->setGroupId($topic);
        $config->setAutoCreateTopic(false);
        $config->setConnectTimeout(5);
        $config->setBootstrapServer($this->manager->getConfig()->get('kafka-default')->bootstrapServers);
        // $config->setMemberId(uniqid());
        $config->setGroupInstanceId(uniqid() . rand(1000, 9999));

        return $config;
    }
}
