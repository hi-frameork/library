<?php

namespace Library\Queue;

use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\ConsumerConfig;

abstract class AbstractConsumer
{
    protected TopicInterface $topic;

    protected ConsumerConfig $config;

    protected string $groupId = '';

    protected string $clientId = '';

    protected string $groupInstanceId = '';

    public function __construct()
    {
        $this->config = new ConsumerConfig();
        $this->config->setGroupId($this->groupId);
        $this->config->setClientId($this->clientId);
        $this->config->setGroupInstanceId($this->groupInstanceId);
        $this->config->setTopic($this->topic->value);
    }

    public function execute(): void
    {
    }


    /**
     * 执行消费业务处理
     */
    abstract public function consume(?ConsumeMessage $message): void;
}
