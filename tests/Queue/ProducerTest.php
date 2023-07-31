<?php

namespace Tests\Queue;

use Library\Queue\Manager;
use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{
    protected Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager([
            'kafka.default' => [
                'bootstrapServers' => '10.43.210.198:9092',
                'brokers'          => '10.43.210.198:9092',
            ],
        ], [
            __DIR__ . '/Stubs',
        ]);
    }

    public function testProducerWithObject()
    {
        $producer = new Stubs\UserOnlineStatusProducer(
            data: [
                'user_id'       => 1,
                'online_status' => 1,
            ],
            batch: false,
        );

        $this->manager->produce($producer);
    }

    public function testProducerWithAlias()
    {
        $this->manager->produce('user_online_status', [
            'user_id'       => 1,
            'online_status' => 1,
        ]);
    }
}
