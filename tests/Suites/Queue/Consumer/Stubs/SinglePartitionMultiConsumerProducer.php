<?php

namespace Tests\Suites\Queue\Consumer\Stubs;

use Library\Queue\AbstractProducer;
use Library\Queue\TopicInterface;

class SinglePartitionMultiConsumerProducer extends AbstractProducer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::SinglePartitionMultiConsumer;
}
