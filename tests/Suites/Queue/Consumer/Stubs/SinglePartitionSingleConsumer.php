<?php

namespace Tests\Suites\Queue\Consumer\Stubs;

use Library\Attribute\Queue\Consumer;
use Library\Queue\AbstractConsumer;
use Library\Queue\TopicInterface;
use longlang\phpkafka\Consumer\ConsumeMessage;

#[Consumer]
class SinglePartitionSingleConsumer extends AbstractConsumer
{
    protected string $connection = 'kafka-default';

    protected TopicInterface $topic = Topic::SinglePartitionSingleConsumer;

    public function consume(?ConsumeMessage $message)
    {
        echo $message->getValue();
    }
}
