<?php

namespace Tests\Queue\Stubs;

use Library\Attribute\Queue\Producer;
use Library\Queue\AbstractProducer;
use Library\Queue\TopicInterface;

#[Producer(alias: 'user_online_status')]
class UserOnlineStatusProducer extends AbstractProducer
{
    protected TopicInterface $topic = Topic::UserOnlineStatus;
}
