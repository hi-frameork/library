<?php

namespace Tests\Suites\Queue\Consumer\Stubs;

use Library\Queue\AbstractConsumer;
use Library\Queue\TopicInterface;
use longlang\phpkafka\Consumer\ConsumeMessage;

class MultiPartitionMultiConsumer extends AbstractConsumer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::MultiPartitionMultiConsumer;

    public function consume(?ConsumeMessage $message)
    {
        echo $message->getValue();
    }
}
