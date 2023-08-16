<?php

namespace Tests\Suites\Queue\Producer;

use Library\Queue\Manager;
use longlang\phpkafka\Client\ClientInterface;
use longlang\phpkafka\Protocol\CreateTopics\CreatableTopic;
use longlang\phpkafka\Protocol\CreateTopics\CreatableTopicResult;
use longlang\phpkafka\Protocol\CreateTopics\CreateTopicsRequest;
use longlang\phpkafka\Protocol\CreateTopics\CreateTopicsResponse;
use Tests\Suites\Queue\TestCase;

class ProducerTest extends TestCase
{
    public function testCreateTopic()
    {
        $client = $this->createKafkaClient();
        $client->connect();

        $request = new CreateTopicsRequest();
        $request->setTopics([
            (new CreatableTopic())
                ->setName('library.producer-cteate-topic-test')
                ->setNumPartitions(3)
                ->setReplicationFactor(-1)
        ]);
        $request->setTimeoutMs(10000);
        $request->setValidateOnly(true);
        $correlationId = $client->send($request);

        $this->assertGreaterThan(0, $correlationId);
    }

    public function testProducerWithObject()
    {
        // $args = $this->createTopic(
        //     'library.producer-test',
        //     3,
        //     -1
        // );

        // /** @var ClientInterface $client */
        // [$client, $correlationId] = $args;

        // try {
        //     /** @var CreateTopicsResponse $response */
        //     $response = $client->recv($correlationId);
        //     /** @var CreatableTopicResult[] $topics */
        //     $topics = $response->getTopics();
        //     $this->assertCount(1, $topics);
        //     // print_r($topics);
        // } finally {
        //     $client->close();
        // }

        // $producer = new Stubs\UserOnlineStatusProducer(
        //     data: [
        //         'user_id'       => 1,
        //         'online_status' => 1,
        //     ],
        //     batch: false,
        // );

        // $this->manager->produce($producer);
    }

    // public function testProducerWithAlias()
    // {
    //     $this->manager->produce('user_online_status', [
    //         'user_id'       => 1,
    //         'online_status' => 1,
    //     ]);
    // }
}
