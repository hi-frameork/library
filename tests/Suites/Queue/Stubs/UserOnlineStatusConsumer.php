<?php

namespace Tests\Queue\Stubs;

use Exception;
use Library\Attribute\Queue\Consumer;
use Library\Queue\AbstractConsumer;
use Library\Queue\TopicInterface;
use longlang\phpkafka\Consumer\ConsumeMessage;

#[Consumer(alias: 'user_online_status')]
class UserOnlineStatusConsumer extends AbstractConsumer
{
    protected TopicInterface $topic = Topic::UserOnlineStatus;

    public function __construct()
    {
        parent::__construct();

        $this->config->setGroupId(__METHOD__);
        // $this->config->setClientId(__METHOD__);
        // $this->config->setGroupInstanceId(__METHOD__);
    }

    public function consume(?ConsumeMessage $message): void
    {
        echo $message->getValue();
        // file_put_contents('php://stdout', $message->getValue() . PHP_EOL);

        $this->stop();

        // $rate = rand(1, 100);
        // if ($rate > 50) {
        //     throw new Exception('test');
        // }

        // file_put_contents('log', $message->getValue() . PHP_EOL, FILE_APPEND);
        // var_dump($message);
        // print_r($message->getValue());
    }
}
