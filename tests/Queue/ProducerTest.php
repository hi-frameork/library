<?php

namespace Tests\Queue;

use PHPUnit\Framework\TestCase;

class ProducerTest extends TestCase
{
    public function testProducerWithObject()
    {
        $producer = new Stubs\UserOnlineStatusProducer(
            data: [
                'user_id' => 1,
                'online_status' => 1,
            ],
            batch: false,
        );

        produce($producer);
    }
}

