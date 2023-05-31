<?php

namespace Tests\Queue\Stubs;

use Library\Attribute\Queue\Consumer;
use Library\Queue\AbstractConsumer;
use Library\Queue\TopicInterface;
use longlang\phpkafka\Consumer\ConsumeMessage;

#[Consumer(
    alias: 'user_online_status',
    number: 1,
)]
class UserOnlineStatusConsumer extends AbstractConsumer
{
    protected TopicInterface $topic = Topic::UserOnlineStatus;

    public function consume(?ConsumeMessage $message): void
    {
    }
}
