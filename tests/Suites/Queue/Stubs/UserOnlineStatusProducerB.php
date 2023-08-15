<?php

namespace Tests\Suites\Queue\Stubs;

use Library\Attribute\Queue\Producer;
use Library\Queue\AbstractProducer;
use Library\Queue\TopicInterface;

#[Producer(alias: 'user_online_status')]
class UserOnlineStatusProducerB extends AbstractProducer
{
    protected TopicInterface $topic = Topic::UserOnlineStatusB;
}
