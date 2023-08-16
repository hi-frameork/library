<?php

namespace Tests\Suites\Queue\Producer\Stubs;

use Library\Queue\AbstractProducer;
use Library\Queue\TopicInterface;

class SimpleProducer extends AbstractProducer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::LibraryProducerCreateTopicTest;

    public function getHeaders(): array
    {
        return [
            'user_id'  => '123',
            'trace_id' => uniqid(),
        ];
    }
}
