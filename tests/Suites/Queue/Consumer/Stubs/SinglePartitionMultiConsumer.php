<?php

namespace Tests\Suites\Queue\Consumer\Stubs;

use Library\Queue\AbstractConsumer;
use Library\Queue\TopicInterface;
use longlang\phpkafka\Consumer\ConsumeMessage;

class SinglePartitionMultiConsumer extends AbstractConsumer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::SinglePartitionMultiConsumer;

    public function consume(?ConsumeMessage $message)
    {
        echo $message->getValue();
    }
}
