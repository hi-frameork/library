<?php

namespace Tests\Suites\Queue\Consumer\Stubs;

use Library\Queue\AbstractProducer;
use Library\Queue\TopicInterface;

class MultiPartitionMultiConsumerProducer extends AbstractProducer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::MultiPartitionMultiConsumer;
}
